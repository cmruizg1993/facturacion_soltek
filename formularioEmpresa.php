<?php
?>
<form action="" method="POST" enctype="multipart/form-data" id="form_business">
    <div class="row">
        <div class="col">
            <div class="form-group"><label for="">Ruc</label><input type="text" name="ruc" id="ruc" class="form-control"></div>
        </div>
        <div class="col">
            <div class="form-group"><label for="">Razón Social</label><input type="text" name="razon" id="razon" class="form-control"></div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="form-group"><label for="">Nombre Comercial</label><input type="text" name="nombre" id="nombre" class="form-control"></div>
        </div>
        <div class="col">
            <div class="form-group"><label for="">Obligado Contabilidad</label>
                <select name="obligado" id="obligado" class="form-control">
                    <option value="NO">NO</option>
                    <option value="SI">SI</option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="form-group"><label for="">Establecimiento</label><input type="text" name="establecimiento" id="establecimiento" class="form-control"></div>
        </div>
        <div class="col">
            <div class="form-group"><label for="">Punto Emisión</label><input type="text" name="punto_emision" id="punto_emision" class="form-control"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-5">
            <div class="form-group"><label for="">Firma Electrónica</label><input type="file" name="p12_file" id="p12_file" class="form-control" accept="application/x-pkcs12 "></div>
        </div>
        <div class="col-5">
            <div class="form-group"><label for="">Contraseña Firma</label><input type="text" name="p12_password" id="p12_password" class="form-control"></div>
        </div>
        <div class="col-2 mt-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="testing" name="testing" value="1">
                <label class="form-check-label" for="testing">
                    Testing
                </label>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col">
            <div class="form-group"><label for="">Dirección</label><input type="text" name="direccion" id="direccion" class="form-control"></div>
        </div>

    </div>
    <div class="row">
        <input type="hidden" name="business" id="business">
        <input type="hidden" name="location" id="location">
        <input type="hidden" name="empresa" id="empresa">
    </div>
    <div class="row justify-content-end mt-3">
        <div class="col-6">
            <button class="btn btn-primary" id="btnGuardar">Guardar</button>
        </div>
    </div>
</form>