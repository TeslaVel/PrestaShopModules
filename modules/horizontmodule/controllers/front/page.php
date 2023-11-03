<?php

class HorizontModulePageModuleFrontController extends ModuleFrontController
{
  public function intiContent() {
    parent::intiContent();

    $this->context->smarty->assign(
      array(
        "data" => 'Mi data desde FC'
      )
    );

    // return $this->display(__FILE__, 'views/templates/front/page.tpl');
    return $this->fetch("module:horizontmodule/views/templates/front/page.tpl");
    // return $this->setTemplate('module:horizontmodule/views/templates/front/page.tpl');
  }

  public function postProcess() {

    if (Tools::isSubmit('btnSubmitFormFc')) {
      return Tools::redirect('');
    }
  }
}