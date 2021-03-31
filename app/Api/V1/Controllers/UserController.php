<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Requests\UserRequest;
use App\Api\V1\Transformers\PermissionTransformer;
use App\Api\V1\Transformers\RoleTransformer;
use App\Api\V1\Transformers\UserTransformer;
use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct()
    {
        $this->middleware('jwt.auth', []);
    }

    public function index(Request $request)
    {
        if (! $request->input('length')) {
            return $this->transformResponse(User::all(), new UserTransformer());
        } else {
            $columns = [
                'id',
                'name',
                'username',
                'email',
            ];

            $length = $request->input('length');
            $draw = $request->input('draw');
            $column = $request->input('column');
            $dir = $request->input('dir');
            $searchValue = $request->input('search');
            $items = User::all();

            if ('asc' == $dir) {
                $items = $items->sortBy($columns[$column]);
            } else {
                $items = $items->sortByDesc($columns[$column]);
            }

            if ('' != $searchValue) {
                $items = $items->filter(function ($item) use ($searchValue) {
                    return stristr($item->name, $searchValue) || stristr($item->email, $searchValue);
                });
            }

            if ($request->input('name')) {
                $name = $request->input('name');
                $items = $items->filter(function ($item) use ($name) {
                    return stristr($item->name, $name);
                });
            }

            if ($request->input('username')) {
                $username = $request->input('username');
                $items = $items->filter(function ($item) use ($username) {
                    return stristr($item->username, $username);
                });
            }

            if ($request->input('email')) {
                $items = $items->where('email', $request->input('email'));
            }

            $total_items = $items->count();

            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $perPage = $length;
            $currentPageSearchResults = $items->slice(($currentPage - 1) * $perPage, $perPage)->all();
            $users = new LengthAwarePaginator($currentPageSearchResults, count($items), $perPage);

            $options = [
                'meta' => ['total' => $total_items],
            ];

            $response = $this->transformResponse($users, new UserTransformer(), $options);

            return $response;
        }
    }

    /**
     * Display a report of the resource.
     *
     * @return array
     */
    public function report(Request $request)
    {
        $pathTmp = sys_get_temp_dir();
        $built_path = env('AWS_BUILD_FOLDER', 'documentator/').'excel/reports/';
        $destinationPath = 'excel/reports/';
        if (! $request->input('length')) {
            $users = User::all();
        } else {
            $columns = [
                'id',
                'name',
                'username',
                'email',
            ];

            $length = $request->input('length');
            $draw = $request->input('draw');
            $column = $request->input('column');
            $dir = $request->input('dir');
            $searchValue = $request->input('search');
            $items = User::all();

            if ('asc' == $dir) {
                $items = $items->sortBy($columns[$column]);
            } else {
                $items = $items->sortByDesc($columns[$column]);
            }

            if ('' != $searchValue) {
                $items = $items->filter(function ($item) use ($searchValue) {
                    return stristr($item->name, $searchValue) || stristr($item->email, $searchValue);
                });
            }

            if ($request->input('name')) {
                $name = $request->input('name');
                $items = $items->filter(function ($item) use ($name) {
                    return stristr($item->name, $name);
                });
            }

            if ($request->input('username')) {
                $username = $request->input('username');
                $items = $items->filter(function ($item) use ($username) {
                    return stristr($item->username, $username);
                });
            }

            if ($request->input('email')) {
                $items = $items->where('email', $request->input('email'));
            }

            $users = $items;
        }

        $excel = Excel::create('Usuarios', function ($excel) use ($users) {
            $excel->setTitle('Reporte de Usuarios');
            $excel->setCreator('Trinitas')->setCompany('Trinitas');
            $excel->setDescription('Lista de Usuarios');
            $excel->sheet('Usuarios', function ($sheet) use ($users) {
                foreach ($users as $user) {
                    $sheet->appendRow([$user->id, $user->name, $user->username, $user->email]);
                }
                $sheet->prependRow(1, ['Id',
                'Nombre',
                'Usuario',
                'Correo', ]);
                $sheet->row(1, function ($row) {
                    $row->setFontColor('#FFFFFF');
                    $row->setBackground('#0EBFE9');
                });
            });
        })->store('xlsx', false, true);

        if (file_exists($excel['full'])) {
            $contentType = 'Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            header('Expires: Mon, 1 Apr 1974 05:00:00 GMT');
            header('Last-Modified: '.gmdate('D,d M YH:i:s').' GMT');
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');
            header($contentType);
            header('Content-Disposition: attachment; filename=reporte_usuarios.xlsx');
            $s3 = Storage::disk('s3');
            $date = Carbon::now('America/Monterrey');
            $date = date_format($date, 'Y-m-d');

            if (! $s3->directories($built_path)) {
                $s3->makeDirectory($built_path);
            }

            $file_name = 'reporte_usuarios'.'-'.$date.'.xlsx';
            $file = new File($excel['full']);
            $s3->putFileAs($built_path, $file, $file_name, 'public');

            return response()->json(['url' => env('AWS_URL', 'https://dev-concentrico.s3.us-west-2.amazonaws.com/documentator/').$destinationPath.$file_name]);
        } else {
            return null;
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(Auth::guard()->user());
    }

    public function show($id)
    {
        return $this->transformResponse(User::find($id), new UserTransformer());
    }

    public function store(UserRequest $request)
    {
        DB::transaction(function () use ($request) {
            $user = new User();
            $user->name = $request->name;
            $user->username = ($request->username) ? $request->username : null;
            $user->email = $request->email;
            $user->isadmin = ($request->isdamin) ? $request->isdamin : 0;
            $user->password = $request->password;

            if ($user->save()) {
                $user->hasRoles()->attach($request->role, ['created_at' => now()]);
            } else {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
            }
        });

        return response()->json(['status' => 'ok', 'message' => __('users.general.user_created')], 200);
    }

    public function update(UserRequest $request, $id)
    {
        DB::beginTransaction();

        try {
            $user = User::find($id);
            if ($request->name && $user->name != $request->name) {
                $user->name = $request->name;
            }
            if ($request->username && $user->username != $request->username) {
                $user->username = $request->username;
            }
            if ($request->email && $user->email != $request->email) {
                $user->email = $request->email;
            }
            if ($request->password) {
                $user->password = $request->password;
            }
            if ($request->isadmin && $user->isadmin != $request->isadmin) {
                $user->isadmin = ($request->isdamin) ? $request->isdamin : 0;
            }
            if ($request->role) {
                $user->hasRoles()->update(['role_id' => $request->role, 'user_has_role.updated_at' => now()]);
            }
            $user->update();

            DB::commit();

            return response()->json(['status' => 'ok', 'message' => __('users.general.user_updated')], 200);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $user = User::find($id);
            if (! $user) {
                return response()->json(
                  ['error' => __('users.general.no_user')], 400);
            }
            $user->delete();
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
        DB::commit();

        return response()->json(['status' => 'ok', 'message' => __('users.general.user_deleted')], 200);
    }

    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $user = User::withTrashed()->findOrFail($id);
            if (! $user) {
                return response()->json(
                  ['error' => __('users.general.no_user')], 400);
            }
            $user->restore();
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
        DB::commit();

        return response()->json(['status' => 'ok', 'message' => __('users.general.user_restored')], 200);
    }

    public function search(Request $request)
    {
        $users = User::query();
        if ($request->query('email')) {
            $users->where('email', 'LIKE', '%'.$request->query('email').'%')->get();
        }

        if ($request->query('name')) {
            $users->where('name', 'LIKE', '%'.$request->query('name').'%')->get();
        }

        if ($request->query('role')) {
            $users->join('user_has_role', 'users.id', 'user_has_role.user_id')->where('user_has_role.role_id', $request->query('role'));
        }

        $users->with('hasRoles')->get();

        return $users->get();
    }

    public function roles()
    {
        return $this->transformResponse(Role::all(), new RoleTransformer());
    }

    public function permissions()
    {
        return $this->transformResponse(Permission::all(), new PermissionTransformer());
    }
}
