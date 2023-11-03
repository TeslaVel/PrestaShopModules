<div class="panel">
  <div class="panel-heading">
    {$title}
  </div>

  <form method="POST">
    <div class="panel-body">
      <p>{$content}</p>

       <div class="pt-2">
          <div class="form-wrapper">
            <label for="valor_input_widget">Valor para Widget fc</label>
            <input type="text" name="valor_input_widget" placeholder="Ingrese valor" class="form-control">
          </div>
      </div>
    </div>
    <div class="panel-footer">
      <button type="submit" name="btnSubmitForm" class="btn btn-default pull-right">
        <i class="process-icon-save"></i>
        Guardar
      </button>
    </div>
  </form>
</div>