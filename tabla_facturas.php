<?php 

    function imprimirTabla($tipoFacturas, $conex, $empresa){
        ?>
            <table class="table">
                <thead>
                <tr>
                    <th>Acciones</th>
                    <th>Id</th>
                    <th>Cliente</th>
                    <th>Cedula</th>
                    <th>Estado</th>
                    <th>Secuencial</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Mail</th>
                    <th>Mensajes</th>
                </tr>
                </thead>
                
                <tbody>
                    <?php 
                    if($empresa){
                        $sql2 = "";
                        if($tipoFacturas == "PENDIENTES"){
                            $sql2 = "SELECT t.id, invoice_no, t.created_at, final_total, f.estado_sri, f.estado_mail, f.respuesta_sri, c.name as cliente, c.contact_id as dni FROM transactions t 
                                    LEFT JOIN contacts c ON c.id = t.contact_id 
                                    LEFT JOIN fe_facturas f ON t.id = f.transaction_id 
                                    WHERE t.business_id = $empresa AND f.id IS NULL 
                                    ORDER BY t.id DESC";
                        }
                        if($tipoFacturas == "ENVIADAS"){
                            $sql2 = "SELECT t.id, invoice_no, t.created_at, final_total, f.estado_sri, f.estado_mail, f.respuesta_sri, c.name as cliente, c.contact_id as dni FROM transactions t 
                                    LEFT JOIN contacts c ON c.id = t.contact_id 
                                    LEFT JOIN fe_facturas f ON t.id = f.transaction_id 
                                    WHERE t.business_id = $empresa AND f.estado_sri = 'RECIBIDA' 
                                    ORDER BY t.id DESC";
                        }
                        if($tipoFacturas == "AUTORIZADAS"){
                            $sql2 = "SELECT t.id, invoice_no, t.created_at, final_total, f.estado_sri, f.estado_mail, f.respuesta_sri, c.name as cliente, c.contact_id as dni FROM transactions t 
                                    LEFT JOIN contacts c ON c.id = t.contact_id 
                                    LEFT JOIN fe_facturas f ON t.id = f.transaction_id 
                                    WHERE t.business_id = $empresa AND f.estado_sri = 'AUTORIZADO' 
                                    ORDER BY t.id DESC";
                        }
                        if($tipoFacturas == "RECHAZADAS"){
                            $sql2 = "SELECT t.id, invoice_no, t.created_at, final_total, f.estado_sri, f.estado_mail, f.respuesta_sri, c.name as cliente, c.contact_id as dni FROM transactions t 
                                    LEFT JOIN contacts c ON c.id = t.contact_id 
                                    LEFT JOIN fe_facturas f ON t.id = f.transaction_id 
                                    WHERE t.business_id = $empresa AND f.estado_sri != 'RECIBIDA' AND f.estado_sri != 'AUTORIZADO' 
                                    ORDER BY t.id DESC";
                        }
                        
                        $resultado2=$conex->query($sql2);
                        while($fila2 = $resultado2->fetch_array()){
                            $id = $fila2['id'];
                            $secuencial = $fila2['invoice_no'];
                            $fecha = ($fila2['created_at']);
                            $total = $fila2['final_total'];
                            $estado = $fila2['estado_sri'];
                            $respuesta = $fila2['respuesta_sri'];
                            $cliente = $fila2['cliente'];
                            $dni = $fila2['dni'];
                            $mail = $fila2['estado_mail'] == 1 ? "ENVIADO":"NO ENVIADO";
                            echo "<tr>";
                            if($tipoFacturas == "PENDIENTES"){
                                echo "<td>
                                    <a class='btn btn-sm btn-info' href='./acciones/enviar_sri.php?id=$id'>Enviar SRI</a>
                                    </td>";
                            }
                            if($tipoFacturas == "ENVIADAS"){
                                echo "<td>
                                    <a class='btn btn-sm btn-primary' href='./acciones/autorizar_sri.php?id=$id'>Autorizar SRI</a>
                                    <a class='btn btn-sm btn-info' href='./acciones/enviar_sri.php?id=$id'>Enviar SRI</a>
                                    </td>";
                            }
                            if($tipoFacturas == "AUTORIZADAS"){
                                echo "<td>
                                    <a class='btn btn-sm btn-info' href='./acciones/enviar_mail.php?id=$id'>Enviar Mail</a>
                                    </td>";
                            }
                            if($tipoFacturas == "RECHAZADAS"){
                                echo "<td>
                                    <a class='btn btn-sm btn-info' href='./acciones/enviar_sri.php?id=$id'>Enviar SRI</a>
                                    </td>";
                            }
                            
                            echo "<td>$id</td>";
                            echo "<td>$cliente</td>";
                            echo "<td>$dni</td>";
                            echo "<td>$estado</td>";
                            echo "<td>$secuencial</td>";
                            echo "<td>$fecha</td>";
                            echo "<td>$total</td>";
                            echo "<td>$mail</td>";
                            echo "<td>$respuesta</td>";
                            echo "</tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        <?php
    }
?>