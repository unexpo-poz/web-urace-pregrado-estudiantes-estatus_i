
function AJAXCrearObjeto(){ 
 var obj; 
 
 if(window.XMLHttpRequest) 
 	{ // no es IE 
 	obj = new XMLHttpRequest(); 
 	} 
	else 
	{ // Es IE o no tiene el objeto 
 		try { 
			 obj = new ActiveXObject("Microsoft.XMLHTTP"); 
		    } 
 		catch (e) { 
 					alert('El navegador utilizado no está soportado'); 
 				  } 
 	} 
 //alert ("objeto creado");
 return obj; 
} 


function fajax(url,capa,valores,metodo,xml) //xml=1 (SI) xml=0 (NO)
{
	
	var ajax=AJAXCrearObjeto();
	var capaContenedora= document.getElementById(capa);
	var contXML;
	/* Creamos y ejecutamos la instancia si el metodo elegido es POST */
	if (metodo.toUpperCase()=='POST')
	{
		
		ajax.open ('POST', url, true);
		ajax.onreadystatechange = function() 
		{
			if (ajax.readyState==1) 
			{
				capaContenedora.innerHTML="";
			}
			else if (ajax.readyState==4)
			{
				if (ajax.status==200)
				{
					if (xml==0)
					{
						document.getElementById(capa).innerHTML=ajax.responseText;
					}
					if (xml==1)
					{

     					var Contxml  = ajax.responseXML.documentElement;
	   					var items = Contxml.getElementsByTagName('nota')[0];
       					var txt = items.getElementsByTagName('destinatario')[0].firstChild.data; 
						document.getElementById(capa).innerHTML=txt;
						
						
					}
				}
				else if (ajax.readyState=404)
				{
					capaContenedora.innerHTML = "La direccion no existe";
				}
				else
				{
					capaContenedora.innerHTML="Error: "+ajax.status;
				}
			}
		}
	
		ajax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		ajax.send(valores);
		return;
	}
	/* Creamos y ejecutamos la instancia si el metodo elegido es GET */
	if (metodo.toUpperCase()=='GET')
	{
		ajax.open ('GET', url, true);
		ajax.onreadystatechange = function() 
		{
			if (ajax.readyState==1) 
			{
				capaContenedora.innerHTML="<img src='loading.gif'>";
			}
			else if (ajax.readyState==4)
			{
				if (ajax.status==200)
				{
					if (xml==0)
					{
						document.getElementById(capa).innerHTML=ajax.responseText;
					}
					if (xml==1)
					{
						alert(ajax.responseXML.getElementsByTagName("nota")[0].childNodes[1].nodeValue); 
					}
				}
				else if (ajax.readyState=404)
				{
					capaContenedora.innerHTML = "La direccion no existe";
				}
				else
				{
					capaContenedora.innerHTML="Error: "+ajax.status;
				}
			}
		}
	
		ajax.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
		ajax.send(null);
		return;
	}
	
	
}
			
			
