<?php
?>

<style>
    .center {
        display: flex;
        justify-content: center;
    }
</style>

 <div class="text-center">
    <object data="<?= $url ?>" type="application/pdf" name="resumen" height="600" width="100%;">
        <param name="src" value="slax.pdf#toolbar=1&amp;navpanes=0&amp;scrollbar=1" />
            <div>
                No se puede abrir el Reporte, haga click en el enlace para descargar <a href="<?= $url ?>">Descargar</a>
            </div>
    </object>
</div>