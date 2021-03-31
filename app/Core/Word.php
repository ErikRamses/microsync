<?php

namespace App\Core;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

class Word
{
    protected $aws_path;
    protected $base_path;
    protected $path;
    protected $s3;
    protected $template;

    /**
     * Create a new Word instance.
     */
    public function __construct()
    {
        $this->aws_path = null;
        $this->path = null;
        $this->template = null;
        $this->s3 = Storage::disk('s3');
        $this->base_path = 'word/';

        if (! $this->s3->directories($this->base_path)) {
            $this->s3->makeDirectory($this->base_path);
        }
    }

    /**
     * Set the template to be used for the current document.
     *
     * @param string $name
     */
    public function setTemplate($name)
    {
        $this->template = new \PhpOffice\PhpWord\TemplateProcessor(storage_path("word-templates/$name.docx"));
    }

    /**
     * Replace template's variables using the pairs key => value from the array.
     *
     * @param array $params
     */
    public function fillTemplate($params)
    {
        foreach ($params as $key => $value) {
            $this->template->setValue($key, $value);
        }
    }

    /**
     * Replace template's variables on tables using arrays.
     *
     * @param array $params
     */
    public function cloneRow($name, $data)
    {
        $this->template->cloneRow($name, $data);
    }

    /**
     * Save the document, using a new path or the previous one.
     *
     * @param string | null $path If path is given, it will change default path
     *
     * @return string
     */
    public function save($path = null)
    {
        if ($path) {
            $this->path = $path;
        }
        if (! $this->template || ! $this->path) {
            return null;
        }

        $tmp_path = sys_get_temp_dir().'/temp.docx';
        $this->template->saveAs($tmp_path);

        $file = new File($tmp_path);
        $a = $this->s3->putFileAs($this->base_path, $file, $this->path.'.docx', 'public');

        $this->aws_path = $this->s3->url($this->base_path.$this->path.'.docx');

        return $this->aws_path;
    }

    /**
     * Get the document path on AWS S3.
     *
     * @return string
     */
    public function getDownloadPath($path = null)
    {
        return $this->aws_path;
    }

    /**
     * Get the file to be downloaded, or null.
     *
     * @param string | null $path If path is given, it will try to retrieve that document
     *
     * @return Response | null
     */
    public function download($path = null)
    {
        if ($this->aws_path) {
            return $this->s3->download($this->base_path.$this->path);
        }

        return null;
    }
}
