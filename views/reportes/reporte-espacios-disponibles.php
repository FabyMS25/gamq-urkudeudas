<?php
?>

<div class="text-center">
    <?= var_dump($url);
    ?>
    <object data="<?= $url ?>" type="application/pdf" name="reporte" height="600" width="100%;">
        <param name="src" value="slax.pdf#toolbar=1&amp;navpanes=0&amp;scrollbar=1" />
            <div>
                No se puede abrir el Reporte, haga click en el enlace para descargar <a href="<?= $url ?>">Descargar</a>
            </div>
    </object>
</div>