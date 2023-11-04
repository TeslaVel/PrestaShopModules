<?php

class HorizontModulePageModuleFrontController extends ModuleFrontController
{
  public function initContent() {
    parent::initContent();

    return $this->setTemplate('module:horizontmodule/views/templates/front/page.tpl');
  }

  public function postProcess() {

    if (Tools::isSubmit('btnSubmitFormFc')) {
      return Tools::redirect('');
    }
  }
}