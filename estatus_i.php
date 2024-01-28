<?php

require_once('../../../../../1051/intensiv/inc/config.php');
require_once('../../../../../1051/intensiv/inc/funciones.php');
require_once('../../../../../1051/intensiv/inc/odbcss_c.php');

$conex = new ODBC_Conn($ODBC,$user,$pass,true,'consulta_estatus_'.date('d-Y').'.log');

$title="Reporte Individual Lapso ".$lapso_preg;

//if ($_SESSION["todoOK"]){
if (true){

		require_once('../../../../../1051/intensiv/enc.php');

	if(isset($_POST['ci_e'])){ // Si envía el numero de cedula
		$ci_e = $_POST['ci_e'];

		$mSQL = "SELECT exp_e FROM dace002 WHERE ci_e='".$ci_e."'";
		$conex->ExecSQL($mSQL,__LINE__,true);
		$existe = ($conex->filas == 1);

		if ($existe){ // si existe el numero de cedula
			$exp = $conex->result[0][0];

			$apellidos="apellidos,apellidos2";
			$nombres="nombres,nombres2";
			$acad="u_cred_aprob_t,ind_acad_t,u_cred_pen_t,c_aprob_equiv_t,u_c_p_indic_t";

			$mSQL = "SELECT exp_e,".$apellidos.",".$nombres.",f_nac_e,l_nac_e,p_nac_e,";
			$mSQL.= "carrera,estatus_e,lapso_in,semestre,pensum,planilla,u_cred_insc, ";
			$mSQL.= "".$acad." ";
			$mSQL.= "FROM dace002 a, tblaca010 b ";
			$mSQL.= "WHERE exp_e='".$exp."' AND a.c_uni_ca=b.c_uni_ca";
			$conex->ExecSQL($mSQL,__LINE__,true);
			$dp_user = $conex->result;

print <<<TABLA001
		<table border="1px" align="center" width="700px" style="border-collapse: collapse;border-color:black;">
				
TABLA001;

				include_once('../../../../../1051/intensiv/dpersonales.php');

			#Busca en DACE006 las asignaturas inscritas según valor de Lapso + EXP
			$mSQL = "SELECT a.c_asigna,b.asignatura,b.unid_credito,a.seccion,a.status ";
			$mSQL.= "FROM dace006 a, tblaca008 b ";
			$mSQL.= "WHERE lapso='".$lapso_preg."' AND exp_e='".$exp."' AND a.c_asigna=b.c_asigna ";
			$mSQL.= "AND status in ('7','A','2','Y','Z','R')";
			
			$mSQL.= " UNION ";

			#Busca en DACE004 y HIS_ACT las asignaturas cargadas según valor de Lapso + EXP
			$mSQL.= "SELECT a.c_asigna,b.asignatura,b.unid_credito,c.his_sec,a.status ";
			$mSQL.= "FROM dace004 a, tblaca008 b, his_act c ";
			$mSQL.= "WHERE lapso='".$lapso_preg."' AND exp_e='".$exp."' AND a.c_asigna=b.c_asigna ";
			$mSQL.= "AND a.acta=c.his_act AND a.lapso=c.his_lap ";
			$mSQL.= "ORDER BY 5 DESC,1";
			
			$conex->ExecSQL($mSQL,__LINE__,true);
			
			$m_user=$conex->result;
			$inscrito=($conex->filas > 0);
				include_once('../../../../../1051/intensiv/mat_monto.php');

				$datos_dep[0][5] = Y_M_D__to__D_M_Y($datos_dep[0][5]); // fecha deposito


	# rutina para buscar los datos del deposito
	$mSQL = "SELECT n_planilla,chequeo,nro,monto_v,opera ";
	$mSQL.= "FROM depositos WHERE exp_e='".$exp."' AND lapso='".$lapso_preg."' AND (OPERA <> 'EXO' or OPERA is null) ORDER BY 1";
	$conex->ExecSQL($mSQL,__LINE__,true);
	$d_user=$conex->result;
print <<<DEPOSITOS1
				<tr>
				<td colspan="3" class="titulo_tabla">Estatus de los dep&oacute;sitos</td>
				</tr>
				<tr>
				<td colspan="3"><br>
					<fieldset class="fieldset">
					<legend class="legend">Dep&oacute;sitos en el Lapso $lapso_preg</legend>
					<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr>
						<td>
							<table border="1px" align="center" width="100%" style="border-collapse: collapse;border-color:black;">
							<tr class="enc_materias">
								<td>#</td>
								<td>N° PLANILLA</td>
								<td>ESTATUS</td>
								<td>N° VALIDACI&Oacute;N</td>
								<td>MONTO</td>
							</tr>
DEPOSITOS1;


	$i=0;
	$total_dep = $conex->filas;
	$monto_b = 0;
	while ($i < $total_dep){
				switch($d_user[$i][1]){//Estatus Deposito
					case '1': case '0'://validado
						$estatus = "VALIDADO";
						$bgcolor = "#33CC00";
						$color	= "#FFFFFF";
						break;
					default:
						$estatus = "NO VALIDADO";
						$bgcolor = "#FFCC00";
						$color	= "#000000";
						break;
				}//Fin switch estatus
	$nro = $i+1;

print<<<DATOS_DEP
				<tr class="datos_materias" onmouseover="this.style.backgroundColor='#FFFF99'" onmouseout="this.style.backgroundColor='#FFFFFF'">
					<td align="center">$nro</td>
					<td align="center">{$d_user[$i][0]}</td>
					<td align="center">
						<span style="color:$color;background:$bgcolor;">&nbsp;{$estatus}&nbsp;</span>
					</td>
					<td align="center">{$d_user[$i][4]}-{$d_user[$i][2]}</td>
					
					<td style="text-align:right;padding-right:5%;">{$d_user[$i][3]}</td>
				</tr>
DATOS_DEP;
				
				if ($d_user[$i][1] != null){
					$monto_b+=$d_user[$i][3];
				}
				$i++;
				
			}//fin while filas

print <<<TOTALES
				<tr class="datos_materias" style="font-weight:bold;font-size:8pt;">
					<td align="right" colspan="4">
						<span style="">&nbsp;SUB TOTAL B&nbsp;</span>
					</td>
					<td style="text-align:right;padding-right:4.8%;">
TOTALES;


printf ("%01.2f", $monto_b);
/*
Formato de número con decimales
La manera de formatear un numero que contiene decimales es usando el comando printf es el que se encarga de indicar al servidor que muestre los numeros de esta forma ayudado por el simbolo % Los digitos los definimos con %01.2f %01: Si lo modificaramos por ej. &02 mostraria nuestro numero con ceros por delante. 2f: indica el numero de decimales.
*/

print <<<TOTALES2
	</td>
				</tr>
						</table>
						</td>
					</tr>
					</table>
					
					</fieldset>
				</td>
				</tr>
				
TOTALES2;

### EXONERACION

# rutina para buscar los datos del deposito
	$mSQL = "SELECT n_planilla,chequeo,nro,monto_v,opera ";
	$mSQL.= "FROM depositos WHERE exp_e='".$exp."' AND lapso='".$lapso_preg."' AND n_planilla <> 'EXONERAD' AND opera = 'EXO' ";
	$conex->ExecSQL($mSQL,__LINE__,true);
	$d_user=$conex->result;
print <<<DEPOSITOS1
				<tr>
				<td colspan="3" class="titulo_tabla">Estatus de la Exoneraci&oacute;n</td>
				</tr>
				<tr>
				<td colspan="3"><br>
					<fieldset class="fieldset">
					<legend class="legend">Exoneraci&oacute;n en el Lapso $lapso_preg</legend>
					<table cellpadding="0" cellspacing="0" border="0" width="100%">
					<tr>
						<td>
							<table border="1px" align="center" width="100%" style="border-collapse: collapse;border-color:black;">
							<tr class="enc_materias">
								<td>#</td>
								<td>N° PLANILLA</td>
								<td>ESTATUS</td>
								<td>N° VALIDACI&Oacute;N</td>
								<td>MONTO</td>
							</tr>
DEPOSITOS1;


	$i=0;
	$total_dep = $conex->filas;
	$monto_c = 0;
	while ($i < $total_dep){
				switch($d_user[$i][1]){//Estatus EXONERACION
					case '1': case '0'://validado
						$estatus = "VALIDADO";
						$bgcolor = "#33CC00";
						$color	= "#FFFFFF";
						$monto_c+=$d_user[$i][3];
						break;
					default:
						$estatus = "NO VALIDADO";
						$bgcolor = "#FFCC00";
						$color	= "#000000";
						break;
				}//Fin switch estatus
	$nro = $i+1;

print<<<DATOS_DEP
				<tr class="datos_materias" onmouseover="this.style.backgroundColor='#FFFF99'" onmouseout="this.style.backgroundColor='#FFFFFF'">
					<td align="center">$nro</td>
					<td align="center">{$d_user[$i][0]}</td>
					<td align="center">
						<span style="color:$color;background:$bgcolor;">&nbsp;{$estatus}&nbsp;</span>
					</td>
					<td align="center">{$d_user[$i][4]}-{$d_user[$i][2]}</td>
					
					<td style="text-align:right;padding-right:5%;">{$d_user[$i][3]}</td>
				</tr>
DATOS_DEP;
				
				
				$i++;
				
			}//fin while filas

print <<<TOTALES
				<tr class="datos_materias" style="font-weight:bold;font-size:8pt;">
					<td align="right" colspan="4">
						<span style="">&nbsp;SUB TOTAL C&nbsp;</span>
					</td>
					<td style="text-align:right;padding-right:4.8%;">
TOTALES;


printf ("%01.2f", $monto_c);
/*
Formato de número con decimales
La manera de formatear un numero que contiene decimales es usando el comando printf es el que se encarga de indicar al servidor que muestre los numeros de esta forma ayudado por el simbolo % Los digitos los definimos con %01.2f %01: Si lo modificaramos por ej. &02 mostraria nuestro numero con ceros por delante. 2f: indica el numero de decimales.
*/

print <<<TOTALES2
	</td>
				</tr>
						</table>
						</td>
					</tr>
					</table>
					
					</fieldset>
				</td>
				</tr>
				
TOTALES2;

### FIN EXONERACION

print <<<TOTALES3
				<tr>
				<td colspan="3" class="titulo_tabla">Total General</td>
				</tr>
				<tr>
				<td colspan="3"><br>
					<fieldset class="fieldset">
					
					<table border="1px" align="center" width="50%" style="border-collapse: collapse;border-color:black;">
						<tr class="enc_materias">
							<td width="60%" style="font-weight:bold;font-size:8pt;"><span style="font-size:7pt;">SUB TOTAL A</span></td>
							<td>
TOTALES3;

printf ("%01.2f", $monto_a);
		
print <<<TOTALES31
							</td>
							<td width="15%">
								DEBE
							</td>
						</tr>
						<tr class="enc_materias">
							<td width="60%" style="font-weight:bold;font-size:8pt;"><span style="font-size:7pt;">SUB TOTAL B + SUB TOTAL C</span></td>
							<td>
TOTALES31;

printf ("%01.2f", $monto_b + $monto_c);
		
print <<<TOTALES32
							</td>
							<td width="15%">
								HABER
							</td>
						</tr>
					</table><br>
					<table border="0px" align="center" width="50%" style="border-collapse: collapse;border-color:black;">
						<tr class="enc_materias">
							<td width="60%"><!-- BALANCE GENERAL --></td>
							<td>
TOTALES32;

$total = ($monto_b + $monto_c) - $monto_a;

if(number_format($total) > 0){
	$monto_x = ($total_mat*($valorMateria - $valorPreMateria));

	$monto_y = ($total_mat - $m_ins)*($valorMateria - $valorPreMateria);

	@$monto_z = ($monto_x - $monto_y)/$m_ins;

	$colort = "#0000FF;";
	$msg = "Este estudiante tiene un excedente de ".number_format($total,2)." Bs. a su favor.<br>";
	//$msg.= "De los cuales ".$monto_z." Bs. son reembolsables.";
	$colormsg = "#5555FF";
	$decoramsg = "none;";

	$msg = "Este estudiante est&aacute; solvente.";
	$colort = "#000000;";
	$colormsg = "#818181";
	$decoramsg = "none;";
	
	

}else if (number_format($total) == 0){
	$msg = "Este estudiante est&aacute; solvente.";
	$colort = "#000000;";
	$colormsg = "#818181";
	$decoramsg = "none;";
}else {
	//$msg = "Este estudiante tiene una morosidad de ".number_format(abs($total),2)." Bs.";
	$msg = "Este estudiante est&aacute; insolvente.";
	$colort = "#FF0000;";
	$colormsg = "#FF5151";
	$decoramsg = "blink;";
}

/*echo "<span style=\"color:$colort\">";
printf ("%01.2f", $total);
echo "</span>";*/

print <<<TOTALES33
							</td>
							<td width="15%">&nbsp;</td>
						</tr>
						<tr>
							<td width="100%" colspan="3" style="text-align:center;font-size:13px;color:$colormsg;text-decoration:$decoramsg"> $msg<br><br>
							
							</td>
						</tr>
					</table>
					</fieldset>
					<br>
				</td>
				</tr>
				</table>
TOTALES33;
	
			

		}else{
print <<<TABLA002
		<table border="1px" align="center" width="700px" style="border-collapse: collapse;border-color:black;">
				<tr>
					<td colspan="3" class="titulo_tabla">Resultado de la Búsqueda</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td width="70%" align="center" valign="middle" style="background-color:#FFFFFF;">
						<br>
						Lo siento el n&uacute;mero de c&eacute;dula <strong>{$_POST['ci_e']}</strong> no ha sido encontrado.<br>
						Por favor verifique e intente nuevamente.<br><br>
					</td>
					<td>&nbsp;</td>
				</tr>
			</table><br>
TABLA002;
		
		}// fin existe deposito

	}// fin isset numero de cedula

if(isset($_POST['valido'])){
	//print_r($_POST);


}// fin isset valido




	#pie
		include_once('../pie.php');
}else{
	$error = mostrar_error('2','2',true);
	include_once('../error.php');
}// Fin todoOK

?>