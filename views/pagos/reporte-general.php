<?php
?>

<style>
    .center {
        display: flex;
        justify-content: center;
    }
</style>
<div class="container center">
    <div class="row">
        <label for="ini" class="col-md-1 col-form-label">Fecha Inicio: </label>
        <div class="col-md-4">
            <input type="date" class="form-control" name="ini">

        </div>
        <label for="fin" class="col-md-1 col-form-label">Fecha Fin: </label>
        <div class="col-md-4">
            <input type="date" class="form-control" name='fin'>
        </div>
        <button class="btn btn-primary">Buscar</button>
    </div>
</div>

<br> <hr>

 <div class="text-center">
        <object data="<?= $url ?>" type="application/pdf" name="comprobante" height="600" width="100%;">
            <param name="src" value="slax.pdf#toolbar=1&amp;navpanes=0&amp;scrollbar=1" />
                <div>
                    No se puede abrir el Reporte, haga click en el enlace para descargar <a href="<?= $url ?>">Descargar</a>
                </div>
        </object>
    </div>