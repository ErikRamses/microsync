<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContractController extends Controller
{
    public function getAportacionEfectivo(Request $request)
    {
        $contract = $request->all();
        $today = Carbon::now();
        setlocale(LC_TIME, 'mx_MX');

        $rules = [
            'no_folio' => 'required',
            'date' => '',
            'lugar' => 'required',
            'amount' => 'required|numeric',
            'comite' => 'required',
            'nombre_completo' => 'required',
            'calle' => 'required',
            'no_exterior' => 'required',
            'no_interior' => '',
            'colonia' => 'required',
            'codigo_postal' => 'required',
            'ciudad' => 'required',
            'clave_ife' => 'required',
            'rfc' => '',
            'folio_registro' => 'required',
            'telefono' => 'required',
            'tipo_cuota' => 'required',
            'responsable' => 'required',
            'convenio' => 'required',
            'referencia' => 'required',
        ];
        $validation = Validator::make($contract, $rules);
        if ($validation->fails()) {
            $errors = $validation->errors();

            return response()->json(['errors' => $errors], 400);
        }

        $contract = (object) $contract;

        $f = new \NumberFormatter('es_MX', \NumberFormatter::SPELLOUT);

        if (! File::exists(public_path().'/documents/')) {
            mkdir(public_path().'/documents/', 0777, true);
        }

        if (! File::exists(public_path().'/documents/file-aportaciones-efectivo/')) {
            mkdir(public_path().'/documents/file-aportaciones-efectivo/', 0777, true);
        }

        $nombre = $contract->no_folio;
        $path = public_path().'/documents/file-aportaciones-efectivo/'.$nombre.'.docx';
        $file_url = '/documents/file-aportaciones-efectivo/'.$nombre.'.docx';

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $settings = new \PhpOffice\PhpWord\Settings();
        $settings->setTempDir(public_path().'/temp');

        $template = new \PhpOffice\PhpWord\TemplateProcessor(public_path().'/../storage/word-templates/Formato_Aportacion_Efectivo.docx');

        $fecha = date('d/m/Y', strtotime($contract->date));
        $amount_string = $this->getAmountAsText($contract->amount);

        $template->setValue('no_folio', $contract->no_folio);
        $template->setValue('lugar', mb_strtoupper($contract->lugar, 'UTF-8'));
        $template->setValue('fecha', $fecha);
        $template->setValue('bueno_por', number_format($contract->amount, 2));
        $template->setValue('comite', $contract->comite);
        $template->setValue('nombre_completo', mb_strtoupper($contract->nombre_completo, 'UTF-8'));
        $template->setValue('domicilio', $contract->calle.' '.$contract->no_exterior.' '.$contract->no_interior.' '.$contract->colonia.' '.$contract->codigo_postal.' '.$contract->ciudad);
        $template->setValue('clave_elector', $contract->clave_ife);
        $template->setValue('rfc', $contract->rfc);
        $template->setValue('folio_registro', $contract->folio_registro);
        $template->setValue('telefono', $contract->telefono);
        $template->setValue('importe_letra', mb_strtoupper($amount_string));
        $template->setValue('ord', ('ordinaria' == $contract->tipo_cuota) ? '◼' : '◻');
        $template->setValue('ex_ord', ('extra-ordinaria' == $contract->tipo_cuota) ? '◼' : '◻');
        $template->setValue('responsable', mb_strtoupper($contract->responsable));
        $template->setValue('convenio', $contract->convenio);
        $template->setValue('referencia', mb_strtoupper($contract->referencia, 'UTF-8'));

        $template->saveAs($path);

        return $request->root().$file_url;
    }

    public function getAportacionEspecie(Request $request)
    {
        $contract = $request->all();
        $today = Carbon::now();
        setlocale(LC_TIME, 'mx_MX');

        $rules = [
            'no_folio' => 'required',
            'date' => '',
            'lugar' => 'required',
            'amount' => 'required|numeric',
            'comite' => 'required',
            'nombre_completo' => 'required',
            'calle' => 'required',
            'no_exterior' => 'required',
            'no_interior' => '',
            'colonia' => 'required',
            'codigo_postal' => 'required',
            'ciudad' => 'required',
            'clave_ife' => 'required',
            'rfc' => '',
            'folio_registro' => 'required',
            'telefono' => 'required',
            'ambito' => 'required',
            'responsable' => 'required',
            'criterio_valuacion' => 'required',
            'bien_aportado' => 'required',
        ];
        $validation = Validator::make($contract, $rules);
        if ($validation->fails()) {
            $errors = $validation->errors();

            return response()->json(['errors' => $errors], 400);
        }

        $contract = (object) $contract;

        if (! File::exists(public_path().'/documents/')) {
            mkdir(public_path().'/documents/', 0777, true);
        }

        if (! File::exists(public_path().'/documents/file-aportaciones-especie/')) {
            mkdir(public_path().'/documents/file-aportaciones-especie/', 0777, true);
        }

        $nombre = $contract->no_folio;
        $path = public_path().'/documents/file-aportaciones-especie/'.$nombre.'.docx';
        $file_url = '/documents/file-aportaciones-especie/'.$nombre.'.docx';

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $settings = new \PhpOffice\PhpWord\Settings();
        $settings->setTempDir(public_path().'/temp');

        $template = new \PhpOffice\PhpWord\TemplateProcessor(public_path().'/../storage/word-templates/Formato_Aportaciones_Especie.docx');

        $fecha = date('d/m/Y', strtotime($contract->date));
        $amount_string = $this->getAmountAsText($contract->amount);

        $template->setValue('no_folio', $contract->no_folio);
        $template->setValue('lugar', mb_strtoupper($contract->lugar, 'UTF-8'));
        $template->setValue('fecha', $fecha);
        $template->setValue('bueno_por', number_format($contract->amount, 2));
        $template->setValue('comite', $contract->comite);
        $template->setValue('nombre_completo', mb_strtoupper($contract->nombre_completo, 'UTF-8'));
        $template->setValue('domicilio', $contract->calle.' '.$contract->no_exterior.' '.$contract->no_interior.' '.$contract->colonia.' '.$contract->codigo_postal.' '.$contract->ciudad);
        $template->setValue('clave_elector', $contract->clave_ife);
        $template->setValue('rfc', $contract->rfc);
        $template->setValue('folio_registro', $contract->folio_registro);
        $template->setValue('telefono', $contract->telefono);
        $template->setValue('importe_letra', mb_strtoupper($amount_string));
        $template->setValue('local', ('local' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('federal', ('federal' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('responsable', mb_strtoupper($contract->responsable));
        $template->setValue('criterio_valuacion', mb_strtoupper($contract->criterio_valuacion, 'UTF-8'));
        $template->setValue('bien_aportado', mb_strtoupper($contract->bien_aportado, 'UTF-8'));

        $template->saveAs($path);

        return $request->root().$file_url;
    }

    public function getGastoEfectivo(Request $request)
    {
        $contract = $request->all();
        $today = Carbon::now();
        setlocale(LC_TIME, 'mx_MX');

        $rules = [
            'no_folio' => 'required',
            'date' => '',
            'lugar' => 'required',
            'amount' => 'required|numeric',
            'comite' => 'required',
            'nombre_completo' => 'required',
            'calle' => 'required',
            'no_exterior' => 'required',
            'no_interior' => '',
            'colonia' => 'required',
            'codigo_postal' => 'required',
            'ciudad' => 'required',
            'clave_ife' => 'required',
            'rfc' => '',
            'folio_registro' => 'required',
            'telefono' => 'required',
            'ambito' => 'required',
            'tipo_cuota' => 'required',
            'responsable' => 'required',
            'convenio' => 'required',
            'referencia' => 'required',
        ];

        $validation = Validator::make($contract, $rules);
        if ($validation->fails()) {
            $errors = $validation->errors();

            return response()->json(['errors' => $errors], 400);
        }

        $contract = (object) $contract;

        $f = new \NumberFormatter('es_MX', \NumberFormatter::SPELLOUT);

        if (! File::exists(public_path().'/documents/')) {
            mkdir(public_path().'/documents/', 0777, true);
        }

        if (! File::exists(public_path().'/documents/file-gasto-efectivo/')) {
            mkdir(public_path().'/documents/file-gasto-efectivo/', 0777, true);
        }

        $nombre = $contract->no_folio;
        $path = public_path().'/documents/file-gasto-efectivo/'.$nombre.'.docx';
        $file_url = '/documents/file-gasto-efectivo/'.$nombre.'.docx';

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $settings = new \PhpOffice\PhpWord\Settings();
        $settings->setTempDir(public_path().'/temp');

        $template = new \PhpOffice\PhpWord\TemplateProcessor(public_path().'/../storage/word-templates/Formato_Gasto_Efectivo.docx');

        $fecha = date('d/m/Y', strtotime($contract->date));
        $amount_string = $this->getAmountAsText($contract->amount);

        $template->setValue('no_folio', $contract->no_folio);
        $template->setValue('lugar', mb_strtoupper($contract->lugar, 'UTF-8'));
        $template->setValue('fecha', $fecha);
        $template->setValue('bueno_por', number_format($contract->amount, 2));
        $template->setValue('comite', $contract->comite);
        $template->setValue('nombre_completo', mb_strtoupper($contract->nombre_completo, 'UTF-8'));
        $template->setValue('domicilio', $contract->calle.' '.$contract->no_exterior.' '.$contract->no_interior.' '.$contract->colonia.' '.$contract->codigo_postal.' '.$contract->ciudad);
        $template->setValue('clave_elector', $contract->clave_ife);
        $template->setValue('rfc', $contract->rfc);
        $template->setValue('folio_registro', $contract->folio_registro);
        $template->setValue('telefono', $contract->telefono);
        $template->setValue('importe_letra', mb_strtoupper($amount_string));
        $template->setValue('ord', ('ordinaria' == $contract->tipo_cuota) ? '◼' : '◻');
        $template->setValue('ex_ord', ('extra-ordinaria' == $contract->tipo_cuota) ? '◼' : '◻');
        $template->setValue('local', ('local' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('federal', ('federal' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('responsable', mb_strtoupper($contract->responsable));
        $template->setValue('convenio', $contract->convenio);
        $template->setValue('referencia', mb_strtoupper($contract->referencia, 'UTF-8'));

        $template->saveAs($path);

        return $request->root().$file_url;
    }

    public function getContratoAportacion(Request $request)
    {
        $contract = $request->all();
        $today = Carbon::now();
        setlocale(LC_TIME, 'mx_MX');

        $rules = [
            'comodante' => 'required',
            'comodato' => 'required',
            'clave_ife' => 'required',
            'rfc' => '',
            'domicilio' => 'required',
            'domicilio_comodatario' => 'required',
            'fecha' => '',
            'monto' => 'required|numeric',
            'dias' => 'required',
            'comodatario' => 'required',
        ];

        $validation = Validator::make($contract, $rules);
        if ($validation->fails()) {
            $errors = $validation->errors();

            return response()->json(['errors' => $errors], 400);
        }

        $contract = (object) $contract;

        $f = new \NumberFormatter('es_MX', \NumberFormatter::SPELLOUT);

        if (! File::exists(public_path().'/documents/')) {
            mkdir(public_path().'/documents/', 0777, true);
        }

        if (! File::exists(public_path().'/documents/file-contrato-aportacion/')) {
            mkdir(public_path().'/documents/file-contrato-aportacion/', 0777, true);
        }

        $nombre = $contract->no_folio;
        $path = public_path().'/documents/file-contrato-aportacion/'.$nombre.'.docx';
        $file_url = '/documents/file-contrato-aportacion/'.$nombre.'.docx';

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $settings = new \PhpOffice\PhpWord\Settings();
        $settings->setTempDir(public_path().'/temp');

        $template = new \PhpOffice\PhpWord\TemplateProcessor(public_path().'/../storage/word-templates/Formato_Contrato_Aportacion.docx');

        $fecha = date('d/m/Y', strtotime($contract->fecha));
        $amount_string = $this->getAmountAsText($contract->monto);

        $template->setValue('fecha', $fecha);
        $template->setValue('monto', number_format($contract->monto, 2));
        $template->setValue('comodante', mb_strtoupper($contract->comodante, 'UTF-8'));
        $template->setValue('comodato', mb_strtoupper($contract->comodato, 'UTF-8'));
        $template->setValue('comodatario', mb_strtoupper($contract->comodatario, 'UTF-8'));
        $template->setValue('domicilio', mb_strtoupper($contract->domicilio, 'UTF-8'));
        $template->setValue('domicilio_comodatario', mb_strtoupper($contract->domicilio_comodatario, 'UTF-8'));
        $template->setValue('clave_elector', $contract->clave_ife);
        $template->setValue('rfc', $contract->rfc);
        $template->setValue('tipo_mueble', $contract->tipo_mueble);
        $template->setValue('dias', $contract->dias);
        $template->setValue('importe_letra', mb_strtoupper($amount_string));
        $template->saveAs($path);

        return $request->root().$file_url;
    }

    public function getAportacionMilitantes(Request $request)
    {
        $contract = $request->all();
        $today = Carbon::now();
        setlocale(LC_TIME, 'mx_MX');

        $rules = [
            'no_folio' => 'required',
            'date' => '',
            'lugar' => 'required',
            'amount' => 'required|numeric',
            'comite' => 'required',
            'nombre_completo' => 'required',
            'calle' => 'required',
            'no_exterior' => 'required',
            'no_interior' => '',
            'colonia' => 'required',
            'codigo_postal' => 'required',
            'ciudad' => 'required',
            'clave_ife' => 'required',
            'rfc' => '',
            'folio_registro' => 'required',
            'telefono' => 'required',
            'ambito' => 'required',
            'responsable' => 'required',
            'criterio_valuacion' => 'required',
            'bien_aportado' => 'required',
            'efectivo_especie' => 'required',
            'militante_candidato' => 'required',
            'cargo' => 'required',
            'formula' => 'required',
            'distrito_local' => 'required',
            'distrito_federal' => 'required',
            'nombre_ayuntamiento' => 'required',
            'otros' => '',
        ];
        $validation = Validator::make($contract, $rules);
        if ($validation->fails()) {
            $errors = $validation->errors();

            return response()->json(['errors' => $errors], 400);
        }

        $contract = (object) $contract;

        $f = new \NumberFormatter('es_MX', \NumberFormatter::SPELLOUT);

        if (! File::exists(public_path().'/documents/')) {
            mkdir(public_path().'/documents/', 0777, true);
        }

        if (! File::exists(public_path().'/documents/file-aportaciones-militantes/')) {
            mkdir(public_path().'/documents/file-aportaciones-militantes/', 0777, true);
        }

        $nombre = $contract->no_folio;
        $path = public_path().'/documents/file-aportaciones-militantes/'.$nombre.'.docx';
        $file_url = '/documents/file-aportaciones-militantes/'.$nombre.'.docx';

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $settings = new \PhpOffice\PhpWord\Settings();
        $settings->setTempDir(public_path().'/temp');

        $template = new \PhpOffice\PhpWord\TemplateProcessor(public_path().'/../storage/word-templates/Formato_Aportaciones_Militantes.docx');

        $fecha = date('d/m/Y', strtotime($contract->date));
        $amount_string = $this->getAmountAsText($contract->amount);

        $template->setValue('no_folio', $contract->no_folio);
        $template->setValue('lugar', mb_strtoupper($contract->lugar, 'UTF-8'));
        $template->setValue('fecha', $fecha);
        $template->setValue('bueno_por', number_format($contract->amount, 2));
        $template->setValue('comite', mb_strtoupper($contract->comite, 'UTF-8'));
        $template->setValue('nombre_completo', mb_strtoupper($contract->nombre_completo, 'UTF-8'));
        $template->setValue('domicilio', $contract->calle.' '.$contract->no_exterior.' '.$contract->no_interior.' '.$contract->colonia.' '.$contract->codigo_postal.' '.$contract->ciudad);
        $template->setValue('clave_elector', mb_strtoupper($contract->clave_ife, 'UTF-8'));
        $template->setValue('rfc', mb_strtoupper($contract->rfc, 'UTF-8'));
        $template->setValue('folio_registro', mb_strtoupper($contract->folio_registro, 'UTF-8'));
        $template->setValue('telefono', $contract->telefono);
        $template->setValue('importe_letra', mb_strtoupper($amount_string, 'UTF-8'));
        $template->setValue('local', ('local' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('federal', ('federal' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('efec_m', ($contract->efectivo_especie and $contract->militante_candidato) ? '◼' : '◻');
        $template->setValue('espe_m', (! $contract->efectivo_especie and $contract->militante_candidato) ? '◼' : '◻');
        $template->setValue('efec_c', ($contract->efectivo_especie and ! $contract->militante_candidato) ? '◼' : '◻');
        $template->setValue('espe_c', (! $contract->efectivo_especie and ! $contract->militante_candidato) ? '◼' : '◻');
        $template->setValue('responsable', mb_strtoupper($contract->responsable, 'UTF-8'));
        $template->setValue('criterio_valuacion', mb_strtoupper($contract->criterio_valuacion, 'UTF-8'));
        $template->setValue('bien_aportado', mb_strtoupper($contract->bien_aportado, 'UTF-8'));
        $template->setValue('presi', ('presidente' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('sena', ('senador' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('dip_fed', ('diputado federal' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('dip_loc', ('diputado local' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('gober', ('gobernador' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('ayun', ('ayuntamiento' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('otros', ('otros' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('formula', mb_strtoupper($contract->formula, 'UTF-8'));
        $template->setValue('dis_fed', mb_strtoupper($contract->distrito_federal, 'UTF-8'));
        $template->setValue('dis_loc', mb_strtoupper($contract->distrito_local, 'UTF-8'));
        $template->setValue('nom_ayun', mb_strtoupper($contract->nombre_ayuntamiento, 'UTF-8'));
        $template->setValue('otros', mb_strtoupper($contract->otros, 'UTF-8'));

        $template->saveAs($path);

        return $request->root().$file_url;
    }

    public function getAportacionMilitantesPrecam(Request $request)
    {
        $contract = $request->all();
        $today = Carbon::now();
        setlocale(LC_TIME, 'mx_MX');

        $rules = [
            'no_folio' => 'required',
            'date' => '',
            'lugar' => 'required',
            'amount' => 'required|numeric',
            'comite' => 'required',
            'nombre_completo' => 'required',
            'calle' => 'required',
            'no_exterior' => 'required',
            'no_interior' => '',
            'colonia' => 'required',
            'codigo_postal' => 'required',
            'ciudad' => 'required',
            'clave_ife' => 'required',
            'rfc' => '',
            'folio_registro' => 'required',
            'telefono' => 'required',
            'ambito' => 'required',
            'responsable' => 'required',
            'criterio_valuacion' => 'required',
            'bien_aportado' => 'required',
            'efectivo_especie' => 'required',
            'militante_candidato' => 'required',
            'cargo' => 'required',
            'formula' => 'required',
            'distrito_local' => 'required',
            'distrito_federal' => 'required',
            'nombre_ayuntamiento' => 'required',
            'otros' => '',
        ];
        $validation = Validator::make($contract, $rules);
        if ($validation->fails()) {
            $errors = $validation->errors();

            return response()->json(['errors' => $errors], 400);
        }

        $contract = (object) $contract;

        $f = new \NumberFormatter('es_MX', \NumberFormatter::SPELLOUT);

        if (! File::exists(public_path().'/documents/')) {
            mkdir(public_path().'/documents/', 0777, true);
        }

        if (! File::exists(public_path().'/documents/file-aportaciones-militantes-precam/')) {
            mkdir(public_path().'/documents/file-aportaciones-militantes-precam/', 0777, true);
        }

        $nombre = $contract->no_folio;
        $path = public_path().'/documents/file-aportaciones-militantes-precam/'.$nombre.'.docx';
        $file_url = '/documents/file-aportaciones-militantes-precam/'.$nombre.'.docx';

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $settings = new \PhpOffice\PhpWord\Settings();
        $settings->setTempDir(public_path().'/temp');

        $template = new \PhpOffice\PhpWord\TemplateProcessor(public_path().'/../storage/word-templates/Formato_Aportaciones_Militantes_Precam.docx');

        $fecha = date('d/m/Y', strtotime($contract->date));
        $amount_string = $this->getAmountAsText($contract->amount);

        $template->setValue('no_folio', $contract->no_folio);
        $template->setValue('lugar', mb_strtoupper($contract->lugar, 'UTF-8'));
        $template->setValue('fecha', $fecha);
        $template->setValue('bueno_por', number_format($contract->amount, 2));
        $template->setValue('comite', mb_strtoupper($contract->comite, 'UTF-8'));
        $template->setValue('nombre_completo', mb_strtoupper($contract->nombre_completo, 'UTF-8'));
        $template->setValue('domicilio', $contract->calle.' '.$contract->no_exterior.' '.$contract->no_interior.' '.$contract->colonia.' '.$contract->codigo_postal.' '.$contract->ciudad);
        $template->setValue('clave_elector', mb_strtoupper($contract->clave_ife, 'UTF-8'));
        $template->setValue('rfc', mb_strtoupper($contract->rfc, 'UTF-8'));
        $template->setValue('folio_registro', mb_strtoupper($contract->folio_registro, 'UTF-8'));
        $template->setValue('telefono', $contract->telefono);
        $template->setValue('importe_letra', mb_strtoupper($amount_string, 'UTF-8'));
        $template->setValue('local', ('local' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('federal', ('federal' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('efec_m', ($contract->efectivo_especie and $contract->militante_candidato) ? '◼' : '◻');
        $template->setValue('espe_m', (! $contract->efectivo_especie and $contract->militante_candidato) ? '◼' : '◻');
        $template->setValue('efec_c', ($contract->efectivo_especie and ! $contract->militante_candidato) ? '◼' : '◻');
        $template->setValue('espe_c', (! $contract->efectivo_especie and ! $contract->militante_candidato) ? '◼' : '◻');
        $template->setValue('responsable', mb_strtoupper($contract->responsable, 'UTF-8'));
        $template->setValue('criterio_valuacion', mb_strtoupper($contract->criterio_valuacion, 'UTF-8'));
        $template->setValue('bien_aportado', mb_strtoupper($contract->bien_aportado, 'UTF-8'));
        $template->setValue('presi', ('presidente' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('sena', ('senador' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('dip_fed', ('diputado federal' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('dip_loc', ('diputado local' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('gober', ('gobernador' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('ayun', ('ayuntamiento' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('otros', ('otros' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('formula', mb_strtoupper($contract->formula, 'UTF-8'));
        $template->setValue('dis_fed', mb_strtoupper($contract->distrito_federal, 'UTF-8'));
        $template->setValue('dis_loc', mb_strtoupper($contract->distrito_local, 'UTF-8'));
        $template->setValue('nom_ayun', mb_strtoupper($contract->nombre_ayuntamiento, 'UTF-8'));
        $template->setValue('otros', mb_strtoupper($contract->otros, 'UTF-8'));

        $template->saveAs($path);

        return $request->root().$file_url;
    }

    public function getAportacionMilitantesCoa(Request $request)
    {
        $contract = $request->all();
        $today = Carbon::now();
        setlocale(LC_TIME, 'mx_MX');

        $rules = [
            'no_folio' => 'required',
            'date' => '',
            'lugar' => 'required',
            'amount' => 'required|numeric',
            'comite' => 'required',
            'nombre_completo' => 'required',
            'calle' => 'required',
            'no_exterior' => 'required',
            'no_interior' => '',
            'colonia' => 'required',
            'codigo_postal' => 'required',
            'ciudad' => 'required',
            'clave_ife' => 'required',
            'rfc' => '',
            'folio_registro' => 'required',
            'telefono' => 'required',
            'ambito' => 'required',
            'responsable' => 'required',
            'criterio_valuacion' => 'required',
            'bien_aportado' => 'required',
            'efectivo_especie' => 'required',
            'militante_candidato' => 'required',
            'cargo' => 'required',
            'formula' => 'required',
            'distrito_local' => 'required',
            'distrito_federal' => 'required',
            'nombre_ayuntamiento' => 'required',
            'otros' => '',
        ];
        $validation = Validator::make($contract, $rules);
        if ($validation->fails()) {
            $errors = $validation->errors();

            return response()->json(['errors' => $errors], 400);
        }

        $contract = (object) $contract;

        $f = new \NumberFormatter('es_MX', \NumberFormatter::SPELLOUT);

        if (! File::exists(public_path().'/documents/')) {
            mkdir(public_path().'/documents/', 0777, true);
        }

        if (! File::exists(public_path().'/documents/file-aportaciones-militantes-coa/')) {
            mkdir(public_path().'/documents/file-aportaciones-militantes-coa/', 0777, true);
        }

        $nombre = $contract->no_folio;
        $path = public_path().'/documents/file-aportaciones-militantes-coa/'.$nombre.'.docx';
        $file_url = '/documents/file-aportaciones-militantes-coa/'.$nombre.'.docx';

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $settings = new \PhpOffice\PhpWord\Settings();
        $settings->setTempDir(public_path().'/temp');

        $template = new \PhpOffice\PhpWord\TemplateProcessor(public_path().'/../storage/word-templates/Formato_Aportaciones_Militantes_Coa.docx');

        $fecha = date('d/m/Y', strtotime($contract->date));
        $amount_string = $this->getAmountAsText($contract->amount);

        $template->setValue('no_folio', $contract->no_folio);
        $template->setValue('lugar', mb_strtoupper($contract->lugar, 'UTF-8'));
        $template->setValue('fecha', $fecha);
        $template->setValue('bueno_por', number_format($contract->amount, 2));
        $template->setValue('comite', mb_strtoupper($contract->comite, 'UTF-8'));
        $template->setValue('nombre_completo', mb_strtoupper($contract->nombre_completo, 'UTF-8'));
        $template->setValue('domicilio', $contract->calle.' '.$contract->no_exterior.' '.$contract->no_interior.' '.$contract->colonia.' '.$contract->codigo_postal.' '.$contract->ciudad);
        $template->setValue('clave_elector', mb_strtoupper($contract->clave_ife, 'UTF-8'));
        $template->setValue('rfc', mb_strtoupper($contract->rfc, 'UTF-8'));
        $template->setValue('folio_registro', mb_strtoupper($contract->folio_registro, 'UTF-8'));
        $template->setValue('telefono', $contract->telefono);
        $template->setValue('importe_letra', mb_strtoupper($amount_string, 'UTF-8'));
        $template->setValue('local', ('local' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('federal', ('federal' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('efec_m', ($contract->efectivo_especie and $contract->militante_candidato) ? '◼' : '◻');
        $template->setValue('espe_m', (! $contract->efectivo_especie and $contract->militante_candidato) ? '◼' : '◻');
        $template->setValue('efec_c', ($contract->efectivo_especie and ! $contract->militante_candidato) ? '◼' : '◻');
        $template->setValue('espe_c', (! $contract->efectivo_especie and ! $contract->militante_candidato) ? '◼' : '◻');
        $template->setValue('responsable', mb_strtoupper($contract->responsable, 'UTF-8'));
        $template->setValue('criterio_valuacion', mb_strtoupper($contract->criterio_valuacion, 'UTF-8'));
        $template->setValue('bien_aportado', mb_strtoupper($contract->bien_aportado, 'UTF-8'));
        $template->setValue('presi', ('presidente' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('sena', ('senador' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('dip_fed', ('diputado federal' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('dip_loc', ('diputado local' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('gober', ('gobernador' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('ayun', ('ayuntamiento' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('otros', ('otros' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('formula', mb_strtoupper($contract->formula, 'UTF-8'));
        $template->setValue('dis_fed', mb_strtoupper($contract->distrito_federal, 'UTF-8'));
        $template->setValue('dis_loc', mb_strtoupper($contract->distrito_local, 'UTF-8'));
        $template->setValue('nom_ayun', mb_strtoupper($contract->nombre_ayuntamiento, 'UTF-8'));
        $template->setValue('otros', mb_strtoupper($contract->otros, 'UTF-8'));

        $template->saveAs($path);

        return $request->root().$file_url;
    }

    public function getAportacionSimpatizantes(Request $request)
    {
        $contract = $request->all();
        $today = Carbon::now();
        setlocale(LC_TIME, 'mx_MX');

        $rules = [
            'no_folio' => 'required',
            'date' => '',
            'lugar' => 'required',
            'amount' => 'required|numeric',
            'comite' => 'required',
            'nombre_completo' => 'required',
            'calle' => 'required',
            'no_exterior' => 'required',
            'no_interior' => '',
            'colonia' => 'required',
            'codigo_postal' => 'required',
            'ciudad' => 'required',
            'rfc' => '',
            'folio_registro' => 'required',
            'telefono' => 'required',
            'ambito' => 'required',
            'responsable' => 'required',
            'criterio_valuacion' => 'required',
            'bien_aportado' => 'required',
            'efectivo_especie' => 'required',
            'cargo' => 'required',
            'formula' => 'required',
            'distrito_local' => 'required',
            'distrito_federal' => 'required',
            'nombre_ayuntamiento' => 'required',
            'otros' => '',
        ];
        $validation = Validator::make($contract, $rules);
        if ($validation->fails()) {
            $errors = $validation->errors();

            return response()->json(['errors' => $errors], 400);
        }

        $contract = (object) $contract;

        $f = new \NumberFormatter('es_MX', \NumberFormatter::SPELLOUT);

        if (! File::exists(public_path().'/documents/')) {
            mkdir(public_path().'/documents/', 0777, true);
        }

        if (! File::exists(public_path().'/documents/file-aportaciones-simpatizantes/')) {
            mkdir(public_path().'/documents/file-aportaciones-simpatizantes/', 0777, true);
        }

        $nombre = $contract->no_folio;
        $path = public_path().'/documents/file-aportaciones-simpatizantes/'.$nombre.'.docx';
        $file_url = '/documents/file-aportaciones-simpatizantes/'.$nombre.'.docx';

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $settings = new \PhpOffice\PhpWord\Settings();
        $settings->setTempDir(public_path().'/temp');

        $template = new \PhpOffice\PhpWord\TemplateProcessor(public_path().'/../storage/word-templates/Formato_Aportaciones_Simpatizantes.docx');

        $fecha = date('d/m/Y', strtotime($contract->date));
        $amount_string = $this->getAmountAsText($contract->amount);

        $template->setValue('no_folio', $contract->no_folio);
        $template->setValue('lugar', mb_strtoupper($contract->lugar, 'UTF-8'));
        $template->setValue('fecha', $fecha);
        $template->setValue('bueno_por', number_format($contract->amount, 2));
        $template->setValue('comite', mb_strtoupper($contract->comite, 'UTF-8'));
        $template->setValue('nombre_completo', mb_strtoupper($contract->nombre_completo, 'UTF-8'));
        $template->setValue('domicilio', $contract->calle.' '.$contract->no_exterior.' '.$contract->no_interior.' '.$contract->colonia.' '.$contract->codigo_postal.' '.$contract->ciudad);
        $template->setValue('rfc', mb_strtoupper($contract->rfc, 'UTF-8'));
        $template->setValue('folio_registro', mb_strtoupper($contract->folio_registro, 'UTF-8'));
        $template->setValue('telefono', $contract->telefono);
        $template->setValue('importe_letra', mb_strtoupper($amount_string, 'UTF-8'));
        $template->setValue('local', ('local' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('federal', ('federal' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('efec_s', ($contract->efectivo_especie) ? '◼' : '◻');
        $template->setValue('espe_s', (! $contract->efectivo_especie) ? '◼' : '◻');
        $template->setValue('responsable', mb_strtoupper($contract->responsable, 'UTF-8'));
        $template->setValue('criterio_valuacion', mb_strtoupper($contract->criterio_valuacion, 'UTF-8'));
        $template->setValue('bien_aportado', mb_strtoupper($contract->bien_aportado, 'UTF-8'));
        $template->setValue('presi', ('presidente' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('sena', ('senador' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('dip_fed', ('diputado federal' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('dip_loc', ('diputado local' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('gober', ('gobernador' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('ayun', ('ayuntamiento' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('otros', ('otros' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('formula', mb_strtoupper($contract->formula, 'UTF-8'));
        $template->setValue('dis_fed', mb_strtoupper($contract->distrito_federal, 'UTF-8'));
        $template->setValue('dis_loc', mb_strtoupper($contract->distrito_local, 'UTF-8'));
        $template->setValue('nom_ayun', mb_strtoupper($contract->nombre_ayuntamiento, 'UTF-8'));
        $template->setValue('otros', mb_strtoupper($contract->otros, 'UTF-8'));

        $template->saveAs($path);

        return $request->root().$file_url;
    }

     public function getAportacionSimpatizantesCoa(Request $request)
    {
        $contract = $request->all();
        $today = Carbon::now();
        setlocale(LC_TIME, 'mx_MX');

        $rules = [
            'no_folio' => 'required',
            'date' => '',
            'lugar' => 'required',
            'amount' => 'required|numeric',
            'comite' => 'required',
            'nombre_completo' => 'required',
            'calle' => 'required',
            'no_exterior' => 'required',
            'no_interior' => '',
            'colonia' => 'required',
            'codigo_postal' => 'required',
            'ciudad' => 'required',
            'rfc' => '',
            'folio_registro' => 'required',
            'telefono' => 'required',
            'ambito' => 'required',
            'responsable' => 'required',
            'criterio_valuacion' => 'required',
            'bien_aportado' => 'required',
            'efectivo_especie' => 'required',
            'cargo' => 'required',
            'formula' => 'required',
            'distrito_local' => 'required',
            'distrito_federal' => 'required',
            'nombre_ayuntamiento' => 'required',
            'otros' => '',
        ];
        $validation = Validator::make($contract, $rules);
        if ($validation->fails()) {
            $errors = $validation->errors();

            return response()->json(['errors' => $errors], 400);
        }

        $contract = (object) $contract;

        $f = new \NumberFormatter('es_MX', \NumberFormatter::SPELLOUT);

        if (! File::exists(public_path().'/documents/')) {
            mkdir(public_path().'/documents/', 0777, true);
        }

        if (! File::exists(public_path().'/documents/file-aportaciones-simpatizantes-coa/')) {
            mkdir(public_path().'/documents/file-aportaciones-simpatizantes-coa/', 0777, true);
        }

        $nombre = $contract->no_folio;
        $path = public_path().'/documents/file-aportaciones-simpatizantes-coa/'.$nombre.'.docx';
        $file_url = '/documents/file-aportaciones-simpatizantes-coa/'.$nombre.'.docx';

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $settings = new \PhpOffice\PhpWord\Settings();
        $settings->setTempDir(public_path().'/temp');

        $template = new \PhpOffice\PhpWord\TemplateProcessor(public_path().'/../storage/word-templates/Formato_Aportaciones_Simpatizantes_Coa.docx');

        $fecha = date('d/m/Y', strtotime($contract->date));
        $amount_string = $this->getAmountAsText($contract->amount);

        $template->setValue('no_folio', $contract->no_folio);
        $template->setValue('lugar', mb_strtoupper($contract->lugar, 'UTF-8'));
        $template->setValue('fecha', $fecha);
        $template->setValue('bueno_por', number_format($contract->amount, 2));
        $template->setValue('comite', mb_strtoupper($contract->comite, 'UTF-8'));
        $template->setValue('nombre_completo', mb_strtoupper($contract->nombre_completo, 'UTF-8'));
        $template->setValue('domicilio', $contract->calle.' '.$contract->no_exterior.' '.$contract->no_interior.' '.$contract->colonia.' '.$contract->codigo_postal.' '.$contract->ciudad);
        $template->setValue('rfc', mb_strtoupper($contract->rfc, 'UTF-8'));
        $template->setValue('folio_registro', mb_strtoupper($contract->folio_registro, 'UTF-8'));
        $template->setValue('telefono', $contract->telefono);
        $template->setValue('importe_letra', mb_strtoupper($amount_string, 'UTF-8'));
        $template->setValue('local', ('local' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('federal', ('federal' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('efec_s', ($contract->efectivo_especie) ? '◼' : '◻');
        $template->setValue('espe_s', (! $contract->efectivo_especie) ? '◼' : '◻');
        $template->setValue('responsable', mb_strtoupper($contract->responsable, 'UTF-8'));
        $template->setValue('criterio_valuacion', mb_strtoupper($contract->criterio_valuacion, 'UTF-8'));
        $template->setValue('bien_aportado', mb_strtoupper($contract->bien_aportado, 'UTF-8'));
        $template->setValue('presi', ('presidente' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('sena', ('senador' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('dip_fed', ('diputado federal' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('dip_loc', ('diputado local' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('gober', ('gobernador' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('ayun', ('ayuntamiento' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('otros', ('otros' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('formula', mb_strtoupper($contract->formula, 'UTF-8'));
        $template->setValue('dis_fed', mb_strtoupper($contract->distrito_federal, 'UTF-8'));
        $template->setValue('dis_loc', mb_strtoupper($contract->distrito_local, 'UTF-8'));
        $template->setValue('nom_ayun', mb_strtoupper($contract->nombre_ayuntamiento, 'UTF-8'));
        $template->setValue('otros', mb_strtoupper($contract->otros, 'UTF-8'));

        $template->saveAs($path);

        return $request->root().$file_url;
    }

    public function getAportacionSimEfecEspecie(Request $request)
    {
        $contract = $request->all();
        $today = Carbon::now();
        setlocale(LC_TIME, 'mx_MX');

        $rules = [
            'no_folio' => 'required',
            'date' => '',
            'lugar' => 'required',
            'amount' => 'required|numeric',
            'comite' => 'required',
            'nombre_completo' => 'required',
            'calle' => 'required',
            'no_exterior' => 'required',
            'no_interior' => '',
            'colonia' => 'required',
            'codigo_postal' => 'required',
            'ciudad' => 'required',
            'rfc' => '',
            'clave_ife' => 'required',
            'folio_registro' => 'required',
            'telefono' => 'required',
            'ambito' => 'required',
            'responsable' => 'required',
            'criterio_valuacion' => 'required',
            'bien_aportado' => 'required',
            'efectivo_especie' => 'required',
            'cargo' => 'required',
            'formula' => 'required',
            'distrito_local' => 'required',
            'distrito_federal' => 'required',
            'nombre_ayuntamiento' => 'required',
            'otros' => '',
        ];
        $validation = Validator::make($contract, $rules);
        if ($validation->fails()) {
            $errors = $validation->errors();

            return response()->json(['errors' => $errors], 400);
        }

        $contract = (object) $contract;

        $f = new \NumberFormatter('es_MX', \NumberFormatter::SPELLOUT);

        if (! File::exists(public_path().'/documents/')) {
            mkdir(public_path().'/documents/', 0777, true);
        }

        if (! File::exists(public_path().'/documents/file-simpatizantes-efect-especie/')) {
            mkdir(public_path().'/documents/file-simpatizantes-efect-especie/', 0777, true);
        }

        $nombre = $contract->no_folio;
        $path = public_path().'/documents/file-simpatizantes-efect-especie/'.$nombre.'.docx';
        $file_url = '/documents/file-simpatizantes-efect-especie/'.$nombre.'.docx';

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $settings = new \PhpOffice\PhpWord\Settings();
        $settings->setTempDir(public_path().'/temp');

        $template = new \PhpOffice\PhpWord\TemplateProcessor(public_path().'/../storage/word-templates/Formato_Simpatizantes_Efect_Especie.docx');

        $fecha = date('d/m/Y', strtotime($contract->date));
        $amount_string = $this->getAmountAsText($contract->amount);

        $template->setValue('no_folio', $contract->no_folio);
        $template->setValue('lugar', mb_strtoupper($contract->lugar, 'UTF-8'));
        $template->setValue('fecha', $fecha);
        $template->setValue('bueno_por', number_format($contract->amount, 2));
        $template->setValue('comite', mb_strtoupper($contract->comite, 'UTF-8'));
        $template->setValue('nombre_completo', mb_strtoupper($contract->nombre_completo, 'UTF-8'));
        $template->setValue('domicilio', $contract->calle.' '.$contract->no_exterior.' '.$contract->no_interior.' '.$contract->colonia.' '.$contract->codigo_postal.' '.$contract->ciudad);
        $template->setValue('rfc', mb_strtoupper($contract->rfc, 'UTF-8'));
        $template->setValue('folio_registro', mb_strtoupper($contract->folio_registro, 'UTF-8'));
        $template->setValue('clave_elector', mb_strtoupper($contract->clave_ife, 'UTF-8'));
        $template->setValue('telefono', $contract->telefono);
        $template->setValue('importe_letra', mb_strtoupper($amount_string, 'UTF-8'));
        $template->setValue('local', ('local' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('federal', ('federal' == $contract->ambito) ? '◼' : '◻');
        $template->setValue('efec_s', ($contract->efectivo_especie) ? '◼' : '◻');
        $template->setValue('espe_s', (! $contract->efectivo_especie) ? '◼' : '◻');
        $template->setValue('responsable', mb_strtoupper($contract->responsable, 'UTF-8'));
        $template->setValue('criterio_valuacion', mb_strtoupper($contract->criterio_valuacion, 'UTF-8'));
        $template->setValue('bien_aportado', mb_strtoupper($contract->bien_aportado, 'UTF-8'));
        $template->setValue('presi', ('presidente' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('sena', ('senador' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('dip_fed', ('diputado federal' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('dip_loc', ('diputado local' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('gober', ('gobernador' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('ayun', ('ayuntamiento' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('otros', ('otros' == $contract->cargo) ? '◼' : '◻');
        $template->setValue('formula', mb_strtoupper($contract->formula, 'UTF-8'));
        $template->setValue('dis_fed', mb_strtoupper($contract->distrito_federal, 'UTF-8'));
        $template->setValue('dis_loc', mb_strtoupper($contract->distrito_local, 'UTF-8'));
        $template->setValue('nom_ayun', mb_strtoupper($contract->nombre_ayuntamiento, 'UTF-8'));
        $template->setValue('otros', mb_strtoupper($contract->otros, 'UTF-8'));

        $template->saveAs($path);

        return $request->root().$file_url;
    }

    public function getFormularioRegistro(Request $request)
    {
        $contract = $request->all();
        $today = Carbon::now();
        setlocale(LC_TIME, 'mx_MX');

        $rules = [
            'partido' => 'required',
            'cargo' => 'required',
            'tipo_candidatura' => 'required',
            'entorno_geo' => 'required',
            'actor_politico' => 'required',
            'sujeto_obligado' => 'required',
            'no_folio' => 'required',
            'date' => '',
            'nombre_completo' => 'required',
            'lugar_nacimiento' => 'required',
            'fecha_nacimiento' => 'required',
            'genero' => 'required',
            'edad' => 'required',
            'clave_ife' => 'required',
            'rfc' => '',
            'curp' => 'required',
            'ocupacion' => 'required',
            'tiempo_residencia' => 'required',
            'telefono' => 'required',
            'tipo_telefono' => 'required',
            'correo' => 'required',
            'total_ingresos' => 'required',
            'total_egresos' => 'required',
            'total_activos' => 'required',
            'total_pasivos' => 'required',
        ];

        $validation = Validator::make($contract, $rules);
        if ($validation->fails()) {
            $errors = $validation->errors();

            return response()->json(['errors' => $errors], 400);
        }

        $contract = (object) $contract;

        $f = new \NumberFormatter('es_MX', \NumberFormatter::SPELLOUT);

        if (! File::exists(public_path().'/documents/')) {
            mkdir(public_path().'/documents/', 0777, true);
        }

        if (! File::exists(public_path().'/documents/file-aceptacion-registro/')) {
            mkdir(public_path().'/documents/file-aceptacion-registro/', 0777, true);
        }

        $nombre = $contract->no_folio;
        $path = public_path().'/documents/file-aceptacion-registro/'.$nombre.'.docx';
        $file_url = '/documents/file-aceptacion-registro/'.$nombre.'.docx';

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $settings = new \PhpOffice\PhpWord\Settings();
        $settings->setTempDir(public_path().'/temp');

        $template = new \PhpOffice\PhpWord\TemplateProcessor(public_path().'/../storage/word-templates/Formulario_Aceptacion_Registro.docx');

        $fecha = date('d/m/Y', strtotime($contract->date));
        //$amount_string = $this->getAmountAsText($contract->amount);

        $template->setValue('partido', mb_strtoupper($contract->partido, 'UTF-8'));
        $template->setValue('cargo', mb_strtoupper($contract->cargo, 'UTF-8'));
        $template->setValue('tipo_candidatura', mb_strtoupper($contract->tipo_candidatura, 'UTF-8'));
        $template->setValue('entorno_geo', mb_strtoupper($contract->entorno_geo, 'UTF-8'));
        $template->setValue('actor_politico', mb_strtoupper($contract->actor_politico, 'UTF-8'));
        $template->setValue('sujeto_obligado', mb_strtoupper($contract->sujeto_obligado, 'UTF-8'));
        $template->setValue('no_folio', $contract->no_folio);
        $template->setValue('fecha_captura', $fecha);

        $template->setValue('nombre', mb_strtoupper($contract->nombre_completo, 'UTF-8'));
        $template->setValue('lugar_nacimiento', mb_strtoupper($contract->lugar_nacimiento, 'UTF-8'));
        $template->setValue('fecha_nacimiento', $contract->fecha_nacimiento);
        $template->setValue('genero', mb_strtoupper($contract->genero, 'UTF-8'));
        $template->setValue('edad', $contract->edad.' AÑOS');
        $template->setValue('clave_elector', mb_strtoupper($contract->clave_ife, 'UTF-8'));
        $template->setValue('rfc', mb_strtoupper($contract->rfc, 'UTF-8'));
        $template->setValue('curp', mb_strtoupper($contract->curp, 'UTF-8'));
        $template->setValue('ocupacion', mb_strtoupper($contract->ocupacion, 'UTF-8'));
        $template->setValue('tiempo_residencia', mb_strtoupper($contract->tiempo_residencia, 'UTF-8'));
        $template->setValue('telefono', $contract->telefono);
        $template->setValue('tipo_telefono', mb_strtoupper($contract->tipo_telefono, 'UTF-8'));
        $template->setValue('correo', mb_strtoupper($contract->correo, 'UTF-8'));
        $template->setValue('total_ingresos', number_format($contract->total_ingresos, 2));
        $template->setValue('total_egresos', number_format($contract->total_egresos, 2));
        $template->setValue('ingresos_egresos', number_format((float) $contract->total_ingresos - (float) $contract->total_egresos, 2));
        $template->setValue('total_activos', number_format($contract->total_activos, 2));
        $template->setValue('total_pasivos', number_format($contract->total_pasivos, 2));
        $template->setValue('activos_pasivos', number_format((float) $contract->total_activos - (float) $contract->total_pasivos, 2));

        $template->saveAs($path);

        return $request->root().$file_url;
    }

    public function getDayString($type = null, $day)
    {
        $f = new \NumberFormatter('es_MX', \NumberFormatter::SPELLOUT);

        switch ($type) {
            case 'normal':
                return ('last_day' != $day) ? $day.' ('.$f->format($day).') de cada mes' : 'último del mes';
                break;

            case 'before':
                return ('last_day' != $day) ? $f->format($day + 1).' del mes anterior' : 'primero del mes';
                break;

            case 'after':
                return ('last_day' != $day) ? $f->format($day) : 'último';
                break;
            default:
                return $f->format($day);
                break;
        }
    }

    public function getRateAsText($rate = 0.0)
    {
        $f = new \NumberFormatter('es_MX', \NumberFormatter::SPELLOUT);
        $rate_int = (int) $rate;
        $rate_with_decimals = explode('.', $rate);
        $rate_decimal = $rate_with_decimals[1] ?? '0';

        return mb_strtolower($f->format($rate_int), 'UTF-8').' punto '.mb_strtolower($f->format($rate_decimal), 'UTF-8');
    }

    public function getAmountAsText($amount = 0.0)
    {
        $f = new \NumberFormatter('es_MX', \NumberFormatter::SPELLOUT);
        $amount_int = (int) $amount;
        $amount_prefix = (0 == ($amount_int % 1000000)) ? ' de' : '';
        $amount_with_decimals = explode('.', $amount);
        $amount_decimal = $amount_with_decimals[1] ?? '00';

        return mb_strtolower($f->format($amount_int), 'UTF-8').$amount_prefix.' pesos '.$amount_decimal.'/100 M.N.';
    }
}
