<div class="panel">
  <div class="panel-heading">
    {$title}
  </div>

  <form method="POST">
    <div class="panel-body">
      <p>{$content}</p>
        <div class="form-wrapper">
          <label for="PS_NEW_CONTENT_1">Texto para contenido</label>
          <input type="text" name="PS_NEW_CONTENT_1" placeholder="Ingrese un contenido" class="form-control">
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