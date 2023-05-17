<?php 

    //header('Content-Type: text/html; charset=UTF-8');

    function imprimirTabla($tipoFacturas, $conex, $business, $location, $filtro = ''){
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
                    if($business > 0 && $location > 0){
                        $sql2 = "";
                        if($tipoFacturas == "PENDIENTES"){
                            $sql2 = "SELECT t.id, invoice_no, t.transaction_date, final_total, f.estado_sri, f.estado_mail, f.respuesta_sri, c.name as cliente, c.contact_id as dni FROM transactions t 
                                    LEFT JOIN contacts c ON c.id = t.contact_id 
                                    LEFT JOIN fe_facturas f ON t.id = f.transaction_id 
                                    WHERE t.business_id = $business AND t.location_id = $location AND f.id IS NULL AND t.type = 'sell' and t.status not LIKE ('draft') and invoice_no not LIKE ('TCK%')
                                    AND ( c.contact_id LIKE '%$filtro%' OR c.name LIKE '%$filtro%' OR invoice_no LIKE '%$filtro%')
                                    ORDER BY t.id DESC LIMIT 25";
                        }
                        if($tipoFacturas == "ENVIADAS"){
                            $sql2 = "SELECT t.id, invoice_no, t.transaction_date, final_total, f.estado_sri, f.estado_mail, f.respuesta_sri, c.name as cliente, c.contact_id as dni FROM transactions t 
                                    LEFT JOIN contacts c ON c.id = t.contact_id 
                                    LEFT JOIN fe_facturas f ON t.id = f.transaction_id 
                                    WHERE t.business_id = $business AND t.location_id = $location AND f.estado_sri = 'RECIBIDA' 
                                    AND ( c.contact_id LIKE '%$filtro%' OR c.name LIKE '%$filtro%' OR invoice_no LIKE '%$filtro%')
                                    ORDER BY t.id DESC LIMIT 25";
                        }
                        if($tipoFacturas == "AUTORIZADAS"){
                            $sql2 = "SELECT t.id, invoice_no, t.transaction_date, final_total, f.estado_sri, f.estado_mail, f.respuesta_sri, c.name as cliente, c.contact_id as dni FROM transactions t 
                                    LEFT JOIN contacts c ON c.id = t.contact_id 
                                    LEFT JOIN fe_facturas f ON t.id = f.transaction_id 
                                    WHERE t.business_id = $business AND t.location_id = $location AND f.estado_sri = 'AUTORIZADO' 
                                    AND ( c.contact_id LIKE '%$filtro%' OR c.name LIKE '%$filtro%' OR invoice_no LIKE '%$filtro%')
                                    ORDER BY t.id DESC LIMIT 25";
                        }
                        if($tipoFacturas == "RECHAZADAS"){
                            $sql2 = "SELECT t.id, invoice_no, t.transaction_date, final_total, f.estado_sri, f.estado_mail, f.respuesta_sri, c.name as cliente, c.contact_id as dni FROM transactions t 
                                    LEFT JOIN contacts c ON c.id = t.contact_id 
                                    LEFT JOIN fe_facturas f ON t.id = f.transaction_id 
                                    WHERE t.business_id = $business AND t.location_id = $location AND f.estado_sri != 'RECIBIDA' AND f.estado_sri != 'AUTORIZADO' 
                                    AND ( c.contact_id LIKE '%$filtro%' OR c.name LIKE '%$filtro%' OR invoice_no LIKE '%$filtro%')
                                    ORDER BY t.id DESC LIMIT 25";
                        }
                        
                        $resultado2=$conex->query($sql2);
                        while($fila2 = $resultado2->fetch_array()){
                            $id = $fila2['id'];
                            $secuencial = $fila2['invoice_no'];
                            $fecha = ($fila2['transaction_date']);
                            $total = $fila2['final_total'];
                            $estado = $fila2['estado_sri'];
                            $respuesta = $fila2['respuesta_sri'];
                            $cliente = ($fila2['cliente']);                           
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
                                    </td>";
                            }
                            if($tipoFacturas == "AUTORIZADAS"){
                                echo "<td>
                                    <a class='btn btn-sm btn-info' href='./acciones/enviar_mail.php?id=$id'>Enviar Mail</a>
                                    <a class='btn btn-sm btn-danger' target='_blank' href='./ride.php?id=$id'>Ride</a>
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