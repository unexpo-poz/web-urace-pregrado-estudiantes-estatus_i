<?php
	include_once('odbc/vImage.php');
    include_once('odbc/odbcss_c.php');
	include_once ('odbc/config.php');
	//include_once ('odbc/activaerror.php');
		
	$vImage = new vImage();
	$vImage->loadCodes();
	
    $datos_p	= array();
    $mat_pre	= array();
    $depositos	= array();
    $fvacio		= TRUE;
    $lapso		= $lapsoProceso;
    $inscribe	= $modoInscripcion;
	$cedYclave	= array();

    function cedula_valida($ced,$clave) {
        
		global $ODBCSS_IP;
		global $vImage;
		global $masterID;
		global $datos_p;

        $ced_v   = false;
        $clave_v = false;
		$encontrado = false;
        if ($ced != ""){
			//Conexion para validar usuario
            $Cusers   = new ODBC_Conn("USERSDB","scael","c0n_4c4");

			//Consulta para buscar los datos del estudiante
            $dSQL = " SELECT ci_e, exp_e, nombres, apellidos, carrera, ";
			$dSQL.= " nombres2, apellidos2 ";
            $dSQL.= " FROM DACE002, TBLACA010 ";
            $dSQL.= " WHERE ci_e='$ced' " ;
			$dSQL.= " AND tblaca010.c_uni_ca=dace002.c_uni_ca";
	
			//foreach($nucleos as $unaSede) {
				unset($Cdatos_p);
				if (!$encontrado) {
					// definimos conexion y ejecutamos consulta de datos
					$Cdatos_p = new ODBC_Conn("CENTURA-DACE","c","c");
  					$Cdatos_p->ExecSQL($dSQL,__LINE__,true);
					if ($Cdatos_p->filas == 1){ //Lo encontro en dace002
						$ced_v = true;  //El numero de cedula existe en UNEXPO
						$uSQL  = "SELECT password FROM usuarios WHERE userid='".$Cdatos_p->result[0][1]."'"; // buscamos el usuario segun el exp_e
						if ($Cusers->ExecSQL($uSQL,__LINE__,true)){
							if ($Cusers->filas == 1)
								$clave_v = ($clave == $Cusers->result[0][0]); // Comparo los password
						}
						if(!$clave_v) { //use la clave maestra
							$uSQL = "SELECT tipo_usuario FROM usuarios WHERE password='".$_POST['contra']."'";
							$Cusers->ExecSQL($uSQL);
							if ($Cusers->filas == 1) {
								$clave_v = (intval($Cusers->result[0][0],10) > 1000);
							}     
						}
						$datos_p = $Cdatos_p->result[0];
						$encontrado = true;
					}
				}
			//}
        }
		// Si falla la autenticacion del usuario, hacemos un retardo
		// para reducir los ataques por fuerza bruta
		if (!($clave_v && $ced_v)) {
			sleep(5); //retardo de 5 segundos
		}			
        return array($ced_v,$clave_v, $vImage->checkCode() );      
    }

    
    function volver_a_indice($vacio,$fueraDeRango, $habilitado=true){
	
    //regresa a la pagina principal:
	global $raizDelSitio, $cedYclave;
    if ($vacio) {
?>
            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
            <META HTTP-EQUIV="Refresh" CONTENT="0;URL=<?php echo $raizDelSitio; ?>">
        </head>
        <body>
        </body>
        </html>
<?php
    }
    else {
?>          <script languaje="Javascript">
            <!--
            function entrar_error() {
<?php
        if ($fueraDeRango) {
			if($habilitado){
?>             
		mensaje = "Lo siento, no puedes inscribirte en este horario.\n";
        mensaje = mensaje + "Por favor, espera tu turno.";
<?php
			}
			else {
?>
	    mensaje = 'Lo siento, no esta habilitado el sistema.';
<?php
			}
		}
        else {
			if(!$cedYclave[0]){
?>
        mensaje = "La cedula no esta registrada o es incorrecta.\n";
<?php
			}	
			else if (!$cedYclave[1]) {
?>
        mensaje = "Clave incorrecta. Por favor intente de nuevo";
<?php
			}
			else if (!$cedYclave[2]) {
?>
        mensaje = "Codigo de seguridad incorrecto. Por favor intente de nuevo";
<?php
			}
		}
?>
                alert(mensaje);
                window.close();
                return true; 
        }

            //-->
            </script>
        </head>
                    <body onload ="return entrar_error();" >

        </body>
<?php 
	global $noCacheFin;
	print $noCacheFin; 
?>
</html>
<?php
    }
}    

    // Programa principal
    //leer las variables enviadas
        
    if(isset($_POST['cedula']) && isset($_POST['contra'])) {
		//print_r($_POST);


        $cedula=$_POST['cedula'];
        $contra=$_POST['contra'];
        // limpiemos la cedula y coloquemos los ceros faltantes
        $cedula = ltrim(preg_replace("/[^0-9]/","",$cedula),'0');
        $cedula = substr("00000000".$cedula, -8);
        $fvacio = false; 
	
		
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta content="text/html; charset=ISO-8859-1" http-equiv="content-type">
<?php
print $noCache; 
print $noJavaScript; 
?>
<title><?php echo $tProceso .' '. $lapso; ?></title>
</head>

<?php
        $cedYclave = cedula_valida($cedula,$contra);
		if(!$fvacio && $cedYclave[0] && $cedYclave[1] && $cedYclave[2]) {
			echo "<body Onload=\"document.envia.action='estatus_i.php'; document.envia.submit();\">";

				

			//include_once('../../../../../1051/intensiv/e_individual/index.php');
			
			// si ingresa muestra toda la informacion.
			//echo $datos_p[1];
print <<<FORM1
			<form name="envia" method="post" action="" >
	 		<input type="hidden" name="ci_e" value="$cedula">
			</form>
FORM1;

         }
        else {
/*print <<<FORM1
			<form name="envia" method="post" action="" >
	 
			</form>
FORM1;*/

			volver_a_indice(false,false); //cedula o clave incorrecta
		}

	}// fin ppal
?>
</body>
</html>