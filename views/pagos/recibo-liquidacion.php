<?php

?>

 <div class="text-center" height="600" width="1024" >
        <object data="<?= $url ?>" type="application/pdf" name="comprobante" height="550" width="100%">
            <param name="src" value="slax.pdf#toolbar=1&amp;navpanes=0&amp;scrollbar=1" />
                <div>
                    No se puede abrir el Reporte, haga click en el enlace para descargar <a href="<?= $url ?>">Descargar</a>
                </div>
        </object>
    </div>