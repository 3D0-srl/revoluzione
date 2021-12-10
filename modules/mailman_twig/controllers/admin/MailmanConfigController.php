<?php

use Marion\Core\Marion;
use Marion\Controllers\ModuleController;

class MailmanConfigController extends ModuleController
{
    public $_auth = 'cms';

    function display()
    {
        $this->setMenu('manage_modules');
        if ($this->isSubmitted()) {
            $dati = $this->getFormdata();
            $array = $this->checkDataForm('mailman_conf', $dati);

            if ($array[0] == 'ok') {
                foreach ($dati as $k => $v) {
                    Marion::setConfig('module_mailman', $k, $v);
                }

                $this->displayMessage('Dati aggiornati con successo');

                Marion::refresh_config();
            } else {
                $this->errors[] = $array[1];
            }
        } else {
            $dati = Marion::getConfig('module_mailman');
        }

        $this->setMenu('mm_config');
        $dataform = $this->getDataForm('mailman_conf', $dati);
        $this->setVar('dataform', $dataform);
        $this->output('mm_setting.htm');
    }
}
