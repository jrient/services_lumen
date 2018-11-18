<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    public $viewParams;

    protected function display($page)
    {
        $data = $this->viewParams;
        $contentFile = $this->viewFilePath($page);
        include_once $this->viewFilePath('common/common');
    }

    private function viewFilePath($file)
    {
        return env('ROOT_PATH').'View/'.$file.'.php';
    }
}
