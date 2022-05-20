// var host="http://observador.cochabamba.bo/utilities/";
var host="http://observador2.cochabamba.bo/servicio/";
var screenH = $.mobile.getScreenHeight();
var middle=(screenH-120)/2;
var latitud=0;
var longitud=0;
var utmX,utmY,zone;
var idUser=0;
var nombreUser="";
var paginaActual="pageLogin";
var pictureSource;
var destinationType;
var idMat=0;
var cantMat=0;
var foto;
var idRecSelect,codCortoRec,comunaRec,reclamanteRec,dirRec,telfRec,fechaR;
var materialesArray=[];
var rastreo;
var watchId;
var popupRastreo=1;
var confirUbi;
$(document).ready(function(){
    // enLinea = navigator.onLine ? true : false;
    // setInterval(function () {
    //     enLinea = navigator.onLine ? true : false;
    // }, 5000);
    // $(document).on("popupafterclose", "#"+paginaActual+" .popupWindow",function( event, ui ) {
    //     $("#"+paginaActual+" .popupWindow .popupContent").html("");
    // });
    var tamFormIni=$("#formIniSesion").height();
    var tamFormLogo=(middle/2)+parseFloat(tamFormIni);
    var marginTopIni=(screenH-(tamFormLogo+tamFormIni))/2;
    // var marginTopIni=middle-tamFormLogo;
    // if (middle<=tamFormLogo) {
    //     marginTopIni=tamFormLogo-middle;
    // }
    $("#loguito img").css("height",(middle/2));
    $("#loguito").css("margin-top",marginTopIni);
    // $("#loguito").text("tamFormulario:"+(parseFloat(middle)-parseFloat(tamFormIni)));
    function showLoad(texto){
        $(".pagina").addClass('ui-disabled');
        $.mobile.loading( "show", {
            text: texto,
            textVisible: true,
            theme: 'b',
            textonly: false,
            html: ''
        });
    }
    function hideLoad(){
        $.mobile.loading("hide");
        $(".pagina").removeClass('ui-disabled');
    }
    function onBackKeyDown(){
        // insertValFuncMap();
        // $.mobile.changePage('#pageIni',{reverse: true, transition: "slide"});
        hideLoad();
        if (paginaActual=="pageEnvio"){
            $.mobile.changePage('#pageInforme',{reverse: true, transition: "slide"});
            paginaActual="pageInforme";
        }else if (paginaActual=="pageLogin"){
            showPopup("Confirmación","Está seguro que desea salir de la aplicación?",{"Salir":"btnSalir","Cancelar":"btnCancel"});
            $(".btnSalir").click(function(){navigator.app.exitApp();});
            $(".btnCancel").click(function(){$("#"+paginaActual+" .popupWindow").popup("close");});
        }else if (paginaActual=="pageReclamos") {
            showPopup("Salir","Desea salir de la aplicación?",{"Salir":"btnCerrar","Cancelar":"btnCancel"});
            $(".btnCerrar").click(function()
            {
                navigator.app.exitApp();
                idUser=0;
                clearInterval(rastreo);
                GPSLocation.clearWatch(watchId);
                $("#txtPass").val("");
                $.mobile.changePage('#pageLogin',{reverse: true, transition: "slide"});
                paginaActual="pageLogin";
            });
            $(".btnCancel").click(function(){$("#"+paginaActual+" .popupWindow").popup("close");});
        }else if (paginaActual=="pageMateriales") {
            $.mobile.changePage('#pageReclamos',{reverse: true, transition: "slide"});
            paginaActual="pageReclamos";
        }else if (paginaActual=="pageInforme") {
            reviseFormSolucionarBack();
            // $.mobile.changePage('#pageReclamos',{reverse: true, transition: "slide"});
            // paginaActual="pageReclamos";
        }
    }
    function enLinea(){
        return navigator.onLine ? true : false;
    }
    function gpsEnabled(){
        // var resGpsEnabled=false;
        cordova.plugins.diagnostic.isGpsLocationEnabled(function(enabled){
            // resGpsEnabled=enabled;
            return enabled;
        }, function(error){
            console.error("El siguiente error ocurrido en : "+error);
        });
    }
    // function showPopup(title,contentText,btnAcep,btnCancel){
    //     $("#titlePopup span").text(title);
    //     $("#popupContent").html('<h3>'+contentText+'</h3>');
    //     if (btnAcep!="" && btnAcep!=null && btnAcep!=undefined) {
    //         $("#popupContent").append('<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b btnAcep" data-transition="flow">'+btnAcep+'</a>');
    //         $(".btnAcept").unbind();
    //     };
    //     if (btnCancel!="" && btnCancel!=null && btnCancel!=undefined) {
    //         $("#popupContent").append('<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b btnCancel" data-rel="back">'+btnCancel+'</a>');
    //         $(".btnCancel").unbind();
    //     };
    //     $("#popupWindow").popup("open");
    // }
    function showPopup(title,contentText,buttons){
        $("#"+paginaActual+" .popupWindow .titlePopup h1").text(title);
        $("#"+paginaActual+" .popupWindow .popupContent").html('<h3>'+contentText+'</h3>');
        if (buttons!=undefined) {
            // $.each(buttons, function(index,button){
            //     $("#popupContent").append('<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b '+button.clase+'" data-transition="flow">'+button.btn+'</a>');
            //     $('.'+button.clase).unbind();
            // });

            for(nameBtn in buttons){
                $("#"+paginaActual+" .popupWindow .popupContent").append('<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b '+buttons[nameBtn]+'" data-transition="flow">'+nameBtn+'</a>');
                $('.'+buttons[nameBtn]).unbind();
            }
        }else{
            $("#"+paginaActual+" .popupWindow .popupContent").append('<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline ui-btn-b btnAceptar" data-transition="flow">Aceptar</a>');
            $('.btnAceptar').click(function(){$("#"+paginaActual+" .popupWindow").popup("close");});
        }
        $("#"+paginaActual+" .popupWindow").popup("open");
    }
    document.addEventListener("deviceready", iniDevice, false);
    function iniDevice(){
        // document.addEventListener("pause", onPause, false);
        // document.addEventListener("resume", onResume, false);
        pictureSource=navigator.camera.PictureSourceType;
        destinationType=navigator.camera.DestinationType;
        document.addEventListener("backbutton", onBackKeyDown, false);
        if (enLinea()) {
            navigator.splashscreen.hide();
            cordova.plugins.diagnostic.isGpsLocationEnabled(function(enabled){
                if (enabled) {
                    $("#formIniSesion").submit(function(event)
                    {
                        var user=$("#txtUser").val();
                        var pass=$("#txtPass").val();

                        
                        if (user=="" || pass=="") 
                        {
                            showPopup("Error de autentificación",'Usted debe ingresar un nombre de usuario y contraseña.');
                        // }else if(latitud==0 || longitud==0){
                        //     showPopup("GPS desactivado",'Se requiere que el GPS esté activo para la aplicación.');
                        }
                        else
                        {
                            showLoad("Autenticando ...");
                            $.ajax({
                                url:host+"getUserAlumb",
                                type:"GET",
                                data:{user_alumb:user,pass_alumb:pass},
                                dataType:"json",
                                success:function(datos){
                                    if (datos.success==1) 
                                    {
                                      
                                        showLoad(datos.message);
                                        $("#map").css("height",middle);
                                        $("#mapContent").css("height",middle);
                                        $("#listaReclamos").css("max-height",middle);
                                        // $("#mapContent").text(screenH+"-"+middle);
                                        idUser=datos.result.id;
                                        nombreUser=datos.result.nombre;
                                      
                                        
                                        setTimeout(function()
                                        {
                                           
                                              CrearMapa();   

                                        }, 200);

                                        loadReclamos();

                                        setTimeout(function()
                                        {
                                         
                                            iniRastreo();
                                            
                                        }, 1500);
                                    }else{
                                        hideLoad();
                                        showPopup("Error en el inicio de sesión",datos.message);
                                    }
                                },
                                error: function(){
                                    hideLoad();
                                    showPopup("Error de conexión","No es posible conectarse con el servidor de la aplicación. Revise que su conexión de datos o Wifi se encuentre funcional.",{"Volver a intentar":"btnIntentar","Salir":"btnSalir"});
                                    $(".btnIntentar").click(function(){
                                        if (enLinea()) {
                                            $("#"+paginaActual+" .popupWindow").popup("close");
                                            $("#formIniSesion").submit();
                                        };
                                    });
                                    $(".btnSalir").click(function(){navigator.app.exitApp();});
                                },
                                timeout:10000
                            });
                        }
                        event.preventDefault();
                    });
                }else{
                    showPopup("GPS desactivado","Usted debe activar el GPS para obtener su ubicación.",{"Activar":"btnActivarGPS","Salir":"btnSalirApp"});
                    $('.btnActivarGPS').click(function(){
                        // Intent callGPSSettingIntent = new Intent(android.provider.Settings.ACTION_LOCATION_SOURCE_SETTINGS);
                        // startActivityForResult(callGPSSettingIntent);
                        window.plugins.SettingOpener.Open("ACTION_LOCATION_SOURCE_SETTINGS",function(){
                            navigator.app.exitApp();
                        },function(e){
                            alert("error: "+e);
                        });
                    });
                    $('.btnSalirApp').click(function(){navigator.app.exitApp();});
                }
            },function(error){
                console.error("The following error occurred: "+error);
            });
        }else{
            showPopup("No es posible conectarse a Internet","Usted debe conectarse a Internet",{"Salir":"btnSalirApp","Volver a intentar":"btnIntentar"});
            $(".btnSalirApp").click(function(){navigator.app.exitApp();});
            $(".btnIntentar").click(function(){
                if (enLinea()) {
                    $("#"+paginaActual+" .popupWindow").popup("close");
                }
            });
        }
    }
    function iniRastreo()
    {
        showLoad("Obteniendo ubicación, manténgase en un lugar despejado...");
        popupRastreo=1;
        watchId = GPSLocation.watchPosition(function(data){
            if (data.coords.latitude!=0) 
            {
                latitud=data.coords.latitude;
                longitud=data.coords.longitude;

                var xy = new Array(2);
                zone = LatLonToUTMXY (DegToRad (parseFloat(latitud)), DegToRad (parseFloat(longitud)), 19, xy);

                var icono="img/marcador_persona.png";    
                var iconFeature = new ol.Feature({
                                                     geometry: new ol.geom.Point([xy[0],xy[1]]),
                                                 });


                var iconStyle = [new ol.style.Style({
                                                  image: new ol.style.Icon(({
                                                  anchor:[0.5,1],
                                                  anchorXUnits: 'fraction',
                                                  anchorYUnits: 'fraction',
                                                  rotateWithView: false,
                                                  scale: 0.8,
                                                  src:icono,
                                                    }))                                                  
                                                }),
                                                  new ol.style.Style({
                                                   text: new ol.style.Text({
                                                   text: nombreUser,
                                                   offsetY: -60,
                                                   stroke: new ol.style.Stroke({color:'#000000' , width: '4'}),
                                                   fill: new ol.style.Fill({color: '#ffffff'})
                                                    })
                                                    }),
                                                   ];    
                               
               
                iconFeature.setStyle(iconStyle);
                iconFeature.set('posicion_usuario','posicion_usuario');
                features_aux= vectorSource.getFeatures();

                if (features_aux != null && features_aux.length > 0) 
                {
                       for (x in features_aux) 
                       {
                           codigo_feature = features_aux[x].get('posicion_usuario');
                           if (codigo_feature == 'posicion_usuario') 
                           {
                               vectorSource.removeFeature(features_aux[x]);
                               break;
                            }
                       }
                 }
                 

                 vectorSource.addFeature(iconFeature);
                 var vectorLayer = new ol.layer.Vector({
                                      source: vectorSource,
                                });
                 map.getLayers().forEach(function(layer, i) 
                 {
                            if (layer.get('name')!=='base_catastro' &&  layer.get('name')!=='capa_limites'  && 
                                          layer.get('name')!=='capa_vias' && layer.get('name')!=='capa_manzanas' && 
                                          layer.get('name')!=='capa_predios' && layer.get('name')!=='capa_otbs'
                                          )
                                map.removeLayer(layer);   
                 });
                 map.addLayer(vectorLayer);
                // extent = vectorLayer.getSource().getExtent();
                // map.getView().fit(extent, map.getSize());

            }
        },function(error){
            console.log("code: "+error.code+"\n"+
                        "message: "+error.message);
            hideLoad();
            // alert("No se puede obtener la ubicación, asegúrese de tener activo el GPS o estar en un lugar despejado.");
            showPopup("Error al obtener su ubicación", "Asegúrese de tener activo el GPS y de ubicarse en un lugar despejado.", {"Volver a intentar":"btnIntentar","Salir":"btnSalirApp"});
            $(".btnSalirApp").click(function(){navigator.app.exitApp();});
            $(".btnIntentar").click(function(){
                $("#"+paginaActual+" .popupWindow").popup("close");
                clearInterval(confirUbi);
                clearInterval(rastreo);
                iniRastreo();
            });
        },{ maximumAge: 2000, timeout: 60000, enableHighAccuracy:true });
        confirUbi=setInterval(function(){
            confirUbicacion();
        },500);
        rastreo=setInterval(function(){
            if (latitud!=0 && longitud!=0 && idUser!=0 && popupRastreo==0) 
            {
                sendRastreo();
                
            }
        },300000);
    }
    function confirUbicacion(){
        if (latitud!=0 && popupRastreo==1) {
            hideLoad();
            clearInterval(confirUbi);
            // alert("desaparece segundo load: "+popupRastreo);
            popupRastreo=0;
        }
    }
    // function onPause(){
    //     clearInterval(rastreo);
    // }
    // function onResume(){
    //     rastreo=setInterval(function(){
    //         if (latitud!=0 && longitud!=0 && idUser!=0) {
    //             sendRastreo();
    //         }
    //     },15000);
    // }
    function sendRastreo(){
        var xy = new Array(2);
        zone = Math.floor ((longitud + 180.0) / 6) + 1
        zone = LatLonToUTMXY (DegToRad (latitud), DegToRad (longitud), zone, xy);
        utmX=xy[0];
        utmY=xy[1];
        $.ajax({
            // url:"http://192.168.220.110/alumbrado/informe/rastreo.php",
            url:host+"loadUbicacionTecnico/",
            type:"POST",
            dataType:"text",
            data:{idUser:idUser,nombre:nombreUser,utmX:utmX,utmY:utmY,zona:zone},
            success:function(datos){
                // alert(datos);
                console.log(datos);
            },
            error: function(){
                console.log("Error de conexión");
            }
        });
    }
    function loadReclamos(){
        $.ajax({
            url:host+"getReclamosAsign/"+idUser+"/",
            // url:"http://localhost/pruebixB/index2.php",
            // url:"http://192.168.1.104/pruebixB/index2.php",
            type:"GET",
            dataType:"json",
            success:function(datos)
            {
                vectorSource.clear();
                showReclamos(datos);

            },
            error: function(){
                hideLoad();
                showPopup("Error de conexión","No es posible conectarse con el servidor de la aplicación. Revise que su conexión de datos o Wifi se encuentre activa y funcional.",{"Volver a intentar":"btnIntentar","Salir":"btnSalir"});
                $(".btnIntentar").click(function(){
                    if (enLinea() && (idUser!=0) )
                    {
                        $("#"+paginaActual+" .popupWindow").popup("close");
                      //  $("#formIniSesion").submit();
                    }
                    else 
                    if ( enLinea() && (idUser==0) )
                    {
                       $("#"+paginaActual+" .popupWindow").popup("close");
                       $("#formIniSesion").submit();   
                    }

                });
                $(".btnSalir").click(function(){navigator.app.exitApp();});
            },
            timeout:10000
        });
    }
    function showReclamos(datos){
        // alert("background esta: "+cordova.plugins.backgroundMode.isActive());
        // $("#mapContent").css("min-height",middle);
        // $("#listaReclamos").css("max-height",middle);
        // $("#mapContent").text(screenH+"-"+middle);

             /*        
                setTimeout(function()
                {
                
                }, 500);
            */

        $("#listaReclamos ul").html('<li data-role="list-divider">'+datos.message+'<span class="ui-li-count">'+datos.result.length+'</span></li>');
        //alert('el datos.success es '+datos.success);        
        if (datos.success==1) 
        {
          
          
            $.each(datos.result, function(index,dato)
            {
                //alert("x: "+parseFloat(dato.point_x)+ " y:"+parseFloat(dato.point_y));
                $("#listaReclamos ul").append(  '<li><a class="reclamo" utm="utm'+dato.id_reclamo+'" x="'+dato.point_x+'" y="'+dato.point_y+'">'+
                                                '<p class="codCortoRec"><b>CÓDIGO: </b><span>'+dato.codigo_corto+'</span></p>'+
                                                '<p class="comunaRec"><b>SUB-ALCALDÍA: </b><span>'+dato.subalcaldia+'</span></p>'+
                                                '<p class="reclamanteRec"><b>RECLAMANTE: </b><span>'+dato.reclamante+'</span></p>'+
                                                '<p class="dirRec" style="white-space:normal"><b>DIRECCIÓN: </b><span>'+dato.detalle+'</span></p>'+
                                                '<p class="ui-li-aside telfRec"><strong>TELÉFONO: </strong><span>'+dato.telefono+'</span></p>'+
                                            '</a>'+
                                            '<a class="solucionar" id="idRec'+dato.id_reclamo+'" fechaR="'+dato.fecha_hora+'">Solucionar</a></li>');
                
                var icono="img/foco_defectuoso1.png";    
                var iconFeature = new ol.Feature({
                                                   geometry: new ol.geom.Point([dato.point_x,dato.point_y]),
                                                 });
                var iconStyle = [new ol.style.Style({
                                                      image: new ol.style.Icon(({
                                                      anchor:[0.5,1],
                                                      anchorXUnits: 'fraction',
                                                      anchorYUnits: 'fraction',
                                                      rotateWithView: false,
                                                      scale: 0.7,
                                                      src:icono,
                                                    }))                                                  
                                                       }),
                                                      new ol.style.Style({
                                                   text: new ol.style.Text({
                                                   text: dato.codigo_corto,
                                                   offsetY: -60,
                                                   stroke: new ol.style.Stroke({color:'#000000' , width: '4'}),
                                                   fill: new ol.style.Fill({color: '#ffffff'})
                                                    })
                                                    }),
                                ];    
                iconFeature.setStyle(iconStyle);
                html_contenido='<b>CÓDIGO: </b><span>'+dato.codigo_corto+'</span><br>'+
                                                '<b>SUB-ALCALDÍA: </b><span>'+dato.subalcaldia+'</span><br>'+
                                                '<b>RECLAMANTE: </b><span>'+dato.reclamante+'</span><br>'+
                                                '<b>DIRECCIÓN: </b><span>'+dato.detalle+'</span><br>'+
                                                '<strong>TELÉFONO: </strong><span>'+dato.telefono+'</span></p>'+'</a>';
                iconFeature.set('html_contenido',html_contenido);
                vectorSource.addFeature(iconFeature);
                
                
            });
            setTimeout(function()
                {
                    extent = vectorLayer.getSource().getExtent();
                    map.getView().fit(extent, map.getSize());
                },50); //delay is in milliseconds 
        }
        $.mobile.changePage( "#pageReclamos", { reverse: false, transition: "slide" } );
        paginaActual="pageReclamos";
        $("#listaReclamos ul li a.reclamo").click(function()
        {
            
            x=$(this).attr("x");
            y=$(this).attr("y");
            
            codCortoRec=$(this).parent("li").children(".reclamo").children(".codCortoRec").children("span").text();
            comunaRec=$(this).parent("li").children(".reclamo").children(".comunaRec").children("span").text();
            reclamanteRec=$(this).parent("li").children(".reclamo").children(".reclamanteRec").children("span").text();
            dirRec=$(this).parent("li").children(".reclamo").children(".dirRec").children("span").text();
            telfRec=$(this).parent("li").children(".reclamo").children(".telfRec").children("span").text();
            var icono="img/foco_defectuoso1.png";  

            var iconFeature = new ol.Feature({
                                            geometry: new ol.geom.Point([x,y]),
                                            });
            var iconStyle = [new ol.style.Style({
                                           image: new ol.style.Icon(({
                                           anchor:[0.5,1],
                                           anchorXUnits: 'fraction',
                                           anchorYUnits: 'fraction',
                                           rotateWithView: false,
                                              scale: 0.7,
                                              src:icono,
                                           }))                                                  
                                           }),
                                            new ol.style.Style({
                                            text: new ol.style.Text({
                                            text: codCortoRec,
                                            offsetY: -70,
                                            stroke: new ol.style.Stroke({color:'#000000' , width: '4'}),
                                            fill: new ol.style.Fill({color: '#ffffff'})
                                            })
                                           }),
                                ];    
            iconFeature.setStyle(iconStyle);

            html_contenido='<b>CÓDIGO: </b><span>'+codCortoRec+'</span><br>'+
            '<b>SUB-ALCALDÍA: </b><span>'+comunaRec+'</span><br>'+
            '<b>RECLAMANTE: </b><span>'+reclamanteRec+'</span><br>'+
            '<b>DIRECCIÓN: </b><span>'+dirRec+'</span><br>'+
            '<strong>TELÉFONO: </strong><span>'+telfRec+'</span></p>'+'</a>';
            iconFeature.set('html_contenido',html_contenido);
            
            vectorSource.clear();
            vectorSource.addFeature(iconFeature);
            // Graficacion del marcador de persona
                                var xy = new Array(2);
                                 zone = LatLonToUTMXY (DegToRad (parseFloat(latitud)), DegToRad (parseFloat(longitud)), 19, xy);
                                 var icono="img/marcador_persona.png";    

                                 var iconFeature = new ol.Feature({
                                                     geometry: new ol.geom.Point([xy[0],xy[1]]),
                                                     
                                                 });
                                 var iconStyle = [new ol.style.Style({
                                                          image: new ol.style.Icon(({
                                                          anchor:[0.5,1],
                                                          anchorXUnits: 'fraction',
                                                          anchorYUnits: 'fraction',
                                                          rotateWithView: false,
                                                          scale: 0.8,
                                                          src:icono,
                                                    }))                                                  
                                                       }),
                                                   
                                                   new ol.style.Style({
                                                   text: new ol.style.Text({
                                                   text: nombreUser,
                                                   offsetY: -60,
                                                   stroke: new ol.style.Stroke({color:'#000000' , width: '4'}),
                                                   fill: new ol.style.Fill({color: '#ffffff'})
                                                    })
                                                    }),
                                                   ];    
                               iconFeature.setStyle(iconStyle);
                               iconFeature.set('posicion_usuario','posicion_usuario');
                               
                               //iconFeature.set('html_contenido',html_contenido);
                               // vectorSource.clear();

                               features_aux= vectorSource.getFeatures();
                               if (features_aux != null && features_aux.length > 0) 
                               {
                                   for (x in features_aux) 
                                   {
                                      codigo_feature = features_aux[x].get('posicion_usuario');
                                      
                                      if (codigo_feature == 'posicion_usuario') 
                                      {
                                          source.removeFeature(features_[x]);
                                          break;
                                      }
                                   }
                               }

                               //alert('hola');

                                vectorSource.addFeature(iconFeature);
                                var vectorLayer = new ol.layer.Vector({
                                      source: vectorSource,
                                });

                                
                                map.getLayers().forEach(function(layer, i) 
                                {
                                       if (layer.get('name')!=='base_catastro' &&  layer.get('name')!=='capa_limites'  && 
                                          layer.get('name')!=='capa_vias' && layer.get('name')!=='capa_manzanas' && 
                                          layer.get('name')!=='capa_predios' && layer.get('name')!=='capa_otbs'
                                          )
                                           map.removeLayer(layer);   
                                });
                                

                                map.addLayer(vectorLayer);
                                setTimeout(function()
                                {
                                    extent = vectorLayer.getSource().getExtent();
                                    map.getView().fit(extent, map.getSize());
                                },50); //delay is in milliseconds 
                                
            // Fin de marcadores de personas
                


        });

        $("#listaReclamos ul li a.solucionar").click(function(){
            idRecSelect=$(this).attr("id");
            fechaR=getFecha($(this).attr("fechaR"));
            codCortoRec=$(this).parent("li").children(".reclamo").children(".codCortoRec").children("span").text();
            comunaRec=$(this).parent("li").children(".reclamo").children(".comunaRec").children("span").text();
            reclamanteRec=$(this).parent("li").children(".reclamo").children(".reclamanteRec").children("span").text();
            dirRec=$(this).parent("li").children(".reclamo").children(".dirRec").children("span").text();
            telfRec=$(this).parent("li").children(".reclamo").children(".telfRec").children("span").text();
            buttonFotoYenvio("foto");
            loadMateriales("edit");
            $("#pageInforme .headInforme h1").text("Reclamo "+codCortoRec);
            $.mobile.changePage( "#pageInforme", { reverse: false, transition: "slide" } );
            paginaActual="pageInforme";
            canvas.fillStyle="#42c615";
            hideLoad();
        });
        $("#listaReclamos ul").listview('refresh');
        hideLoad();
    }
    $(".backReclamos").click(function(){
        if (paginaActual=="pageInforme") {
            reviseFormSolucionarBack();
        }else{
            $.mobile.changePage( "#pageReclamos", { reverse: true, transition: "slide" } );
            paginaActual="pageReclamos";
        }
    });
    $(".backInforme").click(function(){
        $.mobile.changePage( "#pageInforme", { reverse: true, transition: "slide" } );
        paginaActual="pageInforme";
    });
    $("#linkMateriales").click(function(){
        loadMateriales("view");
        $.mobile.changePage( "#pageMateriales", { reverse: false, transition: "slide" } );
        paginaActual="pageMateriales";
        hideLoad();
    });
    $("#refreshReclamos").click(function(){
        showLoad("Actualizando ...");
        loadReclamos();
    })
    $("#adjFoto").click(function(){
        navigator.camera.getPicture(onPhotoDataSuccess, onFail, { quality: 50, destinationType: destinationType.DATA_URL, encodingType: 0, correctOrientation: true });
    });
    function getFecha(fechaHora){
        var date,mes;
        if (fechaHora===undefined) {
            date=new Date();
        }else{
            date=new Date(fechaHora);
        }
        mes=date.getMonth()+1;
        return (date.getDate()<10?"0"+date.getDate():date.getDate())+"/"+(mes<10?"0"+mes:mes)+"/"+date.getFullYear();
    }
    function buttonFotoYenvio(evento){
        $("#fotoYenvio").unbind();
        if (evento=="foto") {
            $("#fotoYenvio").buttonMarkup({icon: "camera"});
            $("#fotoYenvio").text("Foto");
            $("#adjFoto").text("Adjuntar foto");
            $("#fotoYenvio").click(function(){
                navigator.camera.getPicture(onPhotoDataSuccess, onFail, { quality: 50, destinationType: destinationType.DATA_URL, encodingType: 0, correctOrientation: true });
            });
            // $("#fotoYenvio").click(function(){
            //     showPreview();
            //     $.mobile.changePage( "#pageEnvio", { reverse: false, transition: "slide" } );
            //     paginaActual="pageEnvio";
            //     $("#listaMatUsados").table("refresh");
            // });
        }else if (evento=="pre-enviar") {
            $("#fotoYenvio").buttonMarkup({icon: "eye"});
            $("#fotoYenvio").text("Pre-Enviar");
            $("#adjFoto").text("Reemplazar foto");
            // $("#fotoYenvio").click(function(){
            //     $("#fotoSend").attr('src','data:image/jpeg;base64,'+foto);
            //     $.mobile.changePage( "#pageEnvio", { reverse: false, transition: "slide" } );
            //     paginaActual="pageEnvio";
            // });
            $("#fotoYenvio").click(function(){
                showPreview();
                $.mobile.changePage( "#pageEnvio", { reverse: false, transition: "slide" } );
                paginaActual="pageEnvio";
                $("#listaMatUsados").table("refresh");
            });
        };
    }
    // function onPhotoDataSuccess(imageURI){
    // }
    // function onPhotoDataSuccess(imageURI) {
    //     // Uncomment to view the base64-encoded image data
    //     // console.log(imageURI);

    //     // Get image handle
    //     //
    //     var fotoView = document.getElementById('fotoView');

    //     // Unhide image elements
    //     //
    //     // fotoView.style.display = 'block';

    //     // Show the captured photo
    //     // The in-line CSS rules are used to resize the image
    //     //
    //     foto=imageURI;
    //     fotoView.src = "data:image/jpeg;base64," + foto;
    //     buttonFotoYenvio("pre-enviar");
    // }

    var canvas=document.getElementById("fotoView");
    var ctx=canvas.getContext("2d");
    // var cw=canvas.width;
    // var ch=canvas.height;
    var maxW=450;
    var maxH=450;
    // var posXboxClose=0;
    // var posYboxClose=0;
    // var sizeBoxClose=100;
    // var objBoxClose;
    function onPhotoDataSuccess(imageData) {
        // handleFiles(imageData);
        $("#fotoViewImgOrig").attr('src','data:image/jpeg;base64,'+imageData);
        var img=new Image();
        img.onload=function(){
            var iw=img.width;
            var ih=img.height;
            var scale=Math.min((maxW/iw),(maxH/ih));
            var iwScaled=iw*scale;
            var ihScaled=ih*scale;
            canvas.width=iwScaled;
            canvas.height=ihScaled;
            ctx.drawImage(img,0,0,iwScaled,ihScaled);
            // alert(canvas.toDataURL());
            canvas.toDataURL();
            buttonFotoYenvio("pre-enviar");
            // ctx.beginPath();
            // posXboxClose=(iwScaled-sizeBoxClose)/2;
            // posYboxClose=(ihScaled-sizeBoxClose)/2;
            // ctx.rect(posXboxClose,posYboxClose,sizeBoxClose,sizeBoxClose);
            // ctx.lineWidth=3;
            // ctx.fillStyle='#c6c6c6';
            // ctx.fillStyle='rgba(198,198,198,0.5)';
            // ctx.strokeStyle='#4b4b4b';
            // ctx.fill();
            // ctx.moveTo(posXboxClose+10,posYboxClose+10);
            // ctx.lineTo(posXboxClose+sizeBoxClose-10,posYboxClose+sizeBoxClose-10);
            // ctx.moveTo(posXboxClose+sizeBoxClose-10,posYboxClose+10);
            // ctx.lineTo(posXboxClose+10,posYboxClose+sizeBoxClose-10);
            // ctx.stroke();
            // ctx.closePath();
            // objBoxClose={x:posXboxClose,y:posYboxClose,width:sizeBoxClose,height:sizeBoxClose};
        }
        img.src='data:image/jpeg;base64,'+imageData;
        // canvas.addEventListener('click', function(evt){
        //     var mousePos=getMousePos(canvas, evt);
        //     debugger;
        //     if (isInside(mousePos,objBoxClose)) {
        //         alert("click dentro");
        //     }else{
        //         alert("click fuera");
        //     }
        // },false);
    }
    // function getMousePos(canvas, event){
    //     var rect=canvas.getBoundingClientRect();
    //     return {
    //         x:event.clientX-rect.left,
    //         y:event.clientY-rect.top
    //     };
    // }
    // function isInside(pos, box){
    //     return pos.x > box.x && pos.x < box.x+box.width && pos.y < box.y+box.height && pos.y > box.y
    // }
    function onFail(message) {
        console.log('Falla de la foto: ' + message);
    }
    // function handleFiles(imageData){
    //     var img=new Image;
    //     img.onload=function(){
    //         var iw=img.width;
    //         var ih=img.height;
    //         var scale=Math.min((maxW/iw),(maxH/ih));
    //         var iwScaled=iw*scale;
    //         var ihScaled=ih*scale;
    //         canvas.width=iwScaled;
    //         canvas.height=ihScaled;
    //         ctx.drawImage(img,0,0,iwScaled,ihScaled);
    //         alert(canvas.toDataURL());
    //     }
    //     img.src=imageData;
    // }
    function loadMateriales(destino){
        // $("#fotoViewImgOrig").attr('src','img/logo.png');
        // var img=new Image();
        // img.onload=function(){
        //     var iw=img.width;
        //     var ih=img.height;
        //     var scale=Math.min((maxW/iw),(maxH/ih));
        //     var iwScaled=iw*scale;
        //     var ihScaled=ih*scale;
        //     canvas.width=iwScaled;
        //     canvas.height=ihScaled;
        //     ctx.drawImage(img,0,0,iwScaled,ihScaled);
        //     canvas.toDataURL();
        //     buttonFotoYenvio("pre-enviar");
        // }
        // img.src='img/logo.png';

        showLoad("Cargando ...");
        $.ajax({
            url:host+"getMaterialesDisponibles/"+idUser+"/",
            type:"GET",
            dataType:"json",
            success:function(datos){
                if (destino=="view") {
                    $("#listaMateriales tbody").html("");
                    if (datos.success==1) {
                        $.each(datos.result, function(index,dato){
                            $("#listaMateriales tbody").append( '<tr>'+
                                                                    '<td>'+dato.item+'</td><td>'+parseInt(dato.actual)+'</td>'+
                                                                '</tr>');
                            $("#listaMateriales").table("refresh");
                        });
                    }else if (datos.success==0) {
                        $("#listaMateriales tbody").html('<tr><td colspan="2">'+datos.message+'</td></tr>');
                    };

                    // var tablaMateriales=' <table data-role="table" class="ui-responsive movie-list">'+
                    //                                 '<thead>'+
                    //                                     '<tr>'+
                    //                                         '<th>ITEM</th><th>CANTIDAD</th>'+
                    //                                     '</tr>'+
                    //                                 '</thead>'+
                    //                                 '<tbody>';
                    // if (datos.success==1) {
                    //     $.each(datos.result, function(index,dato){
                    //         tablaMateriales= tablaMateriales+'<tr>'+
                    //                                                 '<td>'+dato.item+'</td><td>'+dato.actual+'</td>'+
                    //                                             '</tr>';
                    //     });
                    // }else if (datos.success==0) {
                    //     tablaMateriales= tablaMateriales+'<tr><td colspan="2">'+datos.message+'</td></tr>';
                    // };
                    // $("#listaMateriales").html(tablaMateriales);
                    // $("#listaMateriales").listview('refresh');
                }else if (destino=="edit") {
                    $("#listaMatDisp").html('<li data-role="list-divider">'+datos.message+'<span class="ui-li-count">'+datos.result.length+'</span></li>');
                    if (datos.success==1) {
                        $.each(datos.result, function(index,dato){
                            $("#listaMatDisp").append(   '<li id="idMat'+dato.id_material+'"><a style="white-space:normal" class="material" cantDisp="'+parseInt(dato.actual)+'">'+
                                                                '<p><span class="detMat">'+dato.item+'</span><span class="ui-li-count cantUsada">0</span></p>'+
                                                            '</a>'+
                                                            '<a class="borrarCant">Solucionar</a></li>');
                        });
                        $("#listaMatDisp").listview('refresh');
                        $(".borrarCant").click(function(){
                            // alert("jeje"+$(this).parents("li").attr("id"));
                            idMat=$(this).parent().attr("id");
                            $("#"+idMat+" a .cantUsada").text(0);
                        });
                        $(".material").click(function(){
                            idMat=$(this).parent().attr("id");
                            matNombre=$(this).children().children(".detMat").text();
                            cantMat=parseInt($(this).attr("cantDisp"));

                            
                            showPopup("Ingrese cantidad",'La cantidad disponible del material "'+matNombre+'" es de '+cantMat+'.</br>Cantidad: <input type="number" size="2" class="valCant" value="'+$("#"+idMat+" a .cantUsada").text()+'"></input>',{"Aceptar":"btnInsertCant","Cancelar":"btnCancel"});
                            

                            $(".valCant").select();
                            $(".btnInsertCant").click(function()
                            {
                                if (isNaN($(".valCant").val()) || $(".valCant").val()%1!=0) 
                                {
                                    alert("No es número válido");
                                }
                                else
                                {
                                    if ($(".valCant").val()<=cantMat && parseInt($(".valCant").val())>=0) {
                                        $("#"+idMat+" a .cantUsada").text(parseInt($(".valCant").val()));
                                        $("#"+paginaActual+" .popupWindow").popup("close");
                                    }
                                    else if ($(".valCant").val()>cantMat) 
                                    {
                                        alert("La cantidad no debe sobrepasar de: "+cantMat);
                                    }
                                    else
                                    {
                                        alert("No es un número válido");
                                    }
                                }
                            });
                            $(".btnCancel").click(function()
                            {
                                $("#"+paginaActual+" .popupWindow").popup("close");
                            });
                            
                          
                        });
                        // <li><a style="white-space:normal" href="#popupTemp" data-rel="popup">ALAMBRE GALVANIZADO N 16<span class="ui-li-count">3</span></a><a class="elimMaterial">Solucionar</a></li>
                    }
                };
            },
            error: function(){
                hideLoad();
                showPopup("Error de conexión","No es posible conectarse con el servidor de la aplicación. Revise que su conexión de datos o Wifi se encuentre activa y funcional.",{"Volver a intentar":"btnIntentar","Salir":"btnSalir"});
                $(".btnIntentar").click(function(){
                    if (enLinea()) {
                        $("#"+paginaActual+" .popupWindow").popup("close");
                        loadMateriales();
                    };
                });
                $(".btnSalir").click(function(){navigator.app.exitApp();});
            },
            // complete:function(datos){
            //     $("#listaMateriales table").listview('refresh');
            // },
            timeout:10000
        });
    }
    function reviseFormSolucionarBack(){
        // var materialesArray=[];
        // var materialObj={};
        var matRegis=0;
        $(".material").each(function(){
            if ($(this).children().children(".cantUsada").text()>0) {
                // materialObj={'idMat':$(this).parent().attr("id"),'cantUsada':$(this).children().children().text()};
                // materialesArray.push(materialObj);
                matRegis++;
            };
        });
        if (matRegis>0 || $("#detalleReclamo").val()!="" || canvas.width>0) 
        {
            showPopup("Advertencia","Todos los datos registrados serán eliminados.</br>Está seguro de realizar esta acción?",{"Aceptar":"btnVolver","Cancelar":"btnCancel"});
            $(".btnVolver").click(function(){
                // cleanForm();
                // $.mobile.changePage('#pageReclamos',{reverse: true, transition: "slide"});
                // paginaActual="pageReclamos";
                setTimeout(function()
                           {
                                // loadReclamos();
                                map.updateSize();
                               // alert('fin de cargar reclamos');
                            }, 100);

                        
                        
                            
                            $.mobile.changePage('#pageReclamos',{reverse: true, transition: "slide"});
                            paginaActual="pageReclamos";
                            cleanForm();

                                 
                        
            });
            $(".btnCancel").click(function(){$("#"+paginaActual+" .popupWindow").popup("close");});
        }else{
            setTimeout(function()
                           {
                                // loadReclamos();
                                map.updateSize();
                               // alert('fin de cargar reclamos');
                            }, 100);

                        
                        
                            
                            
                            $.mobile.changePage('#pageReclamos',{reverse: true, transition: "slide"});
                            paginaActual="pageReclamos";
                            cleanForm();

                                 
                       
            // $.mobile.changePage('#pageReclamos',{reverse: true, transition: "slide"});
            // paginaActual="pageReclamos";
        }
    }
    function cleanForm(){
        ctx.clearRect(0,0,canvas.width,canvas.height);
        canvas.width=0;
        ctx.restore();
        $("#detalleReclamo").val("");
    }
    function showPreview(){
        // idRecSelect,codCortoRec,comunaRec,reclamanteRec,dirRec,telfRec
        var imagenCanvas=canvas.toDataURL();
        // $("#datosReclamo").html("");
        // $("#datosReclamo").append(  '<thead><tr><th>CÓDIGO</th><th>FECHA RECLAMO</th><th>FECHA ATENCIÓN</th></tr></thead>'+
        //                             '<tbody><tr>'+
        //                                 '<td>'+codCortoRec+'</td><td>'+fechaR+'</td><td>'+getFecha()+'</td>'+
        //                             '</tr></tbody>'+
        //                             '<thead><tr><th>SUB-ALCALDÍA</th><th>RECLAMANTE</th><th>TELÉFONO</th></tr></thead>'+
        //                             '<tbody><tr>'+
        //                                 '<td>'+comunaRec+'</td><td>'+reclamanteRec+'</td><td>'+telfRec+'</td>'+
        //                             '</tr></tbody>');
        $("#datosReclamo").html("");
        $("#datosReclamo").append( '<thead><tr><td></td><td></td><td></td><td></td></tr></thead>'+
                                    '<tbody><tr>'+
                                        '<td><b>Código:</b></td>'+
                                        '<td>'+codCortoRec+'</td>'+
                                        '<td><b>Fecha de reclamo:</b></td>'+
                                        '<td>'+fechaR+'</td>'+
                                    '</tr><tr>'+
                                        '<td><b>Fecha de atención:</b></td>'+
                                        '<td>'+getFecha()+'</td>'+
                                        '<td><b>Subalcaldía:</b></td>'+
                                        '<td>'+comunaRec+'</td>'+
                                    '</tr><tr>'+
                                        '<td><b>Reclamante:</b></td>'+
                                        '<td>'+reclamanteRec+'</td>'+
                                        '<td><b>Teléfono:</b></td>'+
                                        '<td>'+telfRec+'</td>'+
                                    '</tr></tbody>');
        materialesArray=[];
        var materialObj={};
        $(".material").each(function(){
            if ($(this).children().children(".cantUsada").text()>0) {
                materialObj={'idMat':($(this).parent().attr("id")).slice(5),'cantUsada':$(this).children().children(".cantUsada").text()};
                materialesArray.push(materialObj);
            };
        });
        $("#datosReclamo2").html("");
        $("#datosReclamo2").append( '<thead><tr><td></td><td></td></tr></thead>'+
                                    '<tbody><tr>'+
                                        '<td><b>Dirección:</b></td>'+
                                        '<td>'+dirRec+'</td>'+
                                    '</tr><tr>'+
                                        '<td><b>Estado:</b></td>'+
                                        '<td class="estadoSol">'+($("#solucionado").val()=="si"?"Solucionado":"No solucionado")+'</td>'+
                                    '</tr><tr>'+
                                        '<td><b>Informe técnico:</b></td>'+
                                        '<td>'+$("#detalleReclamo").val()+'</td>'+
                                    '</tr><tr>'+
                                        '<td colspan="2">'+(materialesArray.length>0?"<b>Material empleado</b>":"<b>Material empleado:</b> No se empleó ningún material.")+'</td>'+
                                    '</tr></tbody>');
        // $("#datosReclamo tbody.datos1").append('<tr>'+
        //                                     '<td>'+codCortoRec+'</td><td>'+fechaR+'</td><td>'+getFecha()+'</td>'+
        //                                 '</tr>');
        // $("#datosReclamo tbody.datos2").append('<tr>'+
        //                                     '<td>'+comunaRec+'</td><td>'+reclamanteRec+'</td><td>'+telfRec+'</td>'+
        //                                 '</tr>');

        $("#datosReclamo2 .estadoSol").css("color",($("#solucionado").val()=="si"?"green":"red"));
        $("#fotoSend").attr('src',imagenCanvas);
        // $("#lblCodigoPrev").text(codCortoRec);
        // $("#lblFechaRprev").text(fechaR);
        // $("#lblFechaAprev").text(getFecha());
        // $("#lblComunaPrev").html((comunaRec!=""?comunaRec:"</br>"));
        // $("#lblReclamantePrev").text(reclamanteRec);
        // $("#lblTelfPrev").text(telfRec);
        // $("#lblDirPrev").text(dirRec);
        // $("#lblEstadoPrev").text(($("#solucionado").val()=="si"?"SOLUCIONADO":"NO SOLUCIONADO"));
        // $("#lblInfPrev").text($("#detalleReclamo").val());
        if (materialesArray.length>0) {
            // $("#lblInfMatPrev").html('<b>MATERIAL EMPLEADO</b>');
            $("#listaMatUsados").show();
            $("#listaMatUsados tbody").html('');
            $.each(materialesArray, function(index,dato){
                $("#listaMatUsados tbody").append(  '<tr>'+
                                                        '<td>'+$("#idMat"+dato.idMat).children().children().children(".detMat").text()+'</td><td>'+dato.cantUsada+'</td>'+
                                                    '</tr>');
                // $("#listaMatUsados").table("refresh");
                // $("#datosReclamo").table("refresh");
            });
            // $('#listaMatUsados').html(  '<div class="ui-grid-a">'+
            //                                 '<div class="ui-block-a"><div class="ui-body ui-body-a"><b>ITEM</b></div></div>'+
            //                                 '<div class="ui-block-b"><div class="ui-body ui-body-a"><b>CANTIDAD</b></div></div>'+
            //                             '</div>');
            // for (var i = 0; i < materialesArray.length; i++) {
            //     $("#listaMatUsados").append('<div class="ui-grid-a">'+
            //                                     '<div class="ui-block-a"><div class="ui-body ui-body-a">'+$("#idMat"+materialesArray[i].idMat).children().children().children(".detMat").text()+'</div></div>'+
            //                                     '<div class="ui-block-b"><div class="ui-body ui-body-a">'+materialesArray[i].cantUsada+'</div></div>'+
            //                                 '</div>');
            // };
        }else{
            // $("#lblInfMatPrev").html('<b>MATERIAL EMPLEADO:</b> No se empleó ningún material.');
            $("#listaMatUsados").hide();
        }
        // setTimeout(function(){
        //     $("#datosReclamo").table("refresh");
        // }, 1000);
    }
    $("#enviarSol").click(function(){
        cordova.plugins.diagnostic.isGpsLocationEnabled(function(enabled){
            if (enabled) {
                showLoad("Enviando ...");
                var imagenCanvas=canvas.toDataURL();
                imagenCanvas=imagenCanvas.substring(22);//data:image/png;base64,
                $.ajax({
                    // url:host+"getReclamosAsign/"+idUser+"/",
                    // url:"http://localhost/pruebixB/index2.php",
                    // url:"http://localhost/CEAB/mobil/login/alumbrado.php",
                    // url:"http://192.168.1.104/CEAB/mobil/login/alumbrado.php",
                    url:host+"updateInfoReclamo/",
                    // url:"http://192.168.220.110/alumbrado/informe/alumbrado.php",
                    // url:"http://localhost/alumbrado/informe/alumbrado.php",
                    type:"POST",
                    dataType:"text",
                    data:{idUser:idUser,idRec:idRecSelect.substring(5),informe:$("#detalleReclamo").val(),sol:$("#solucionado").val(),foto:imagenCanvas,materiales:JSON.stringify(materialesArray)},
                    // data:{idUser:idUser,idRec:idRecSelect.substring(5),foto:imagenCanvas},
                    success:function(datos)
                    {
                        // alert(datos);
                        showLoad("Envío exitoso");
                        setTimeout(function()
                           {
                                loadReclamos();
                                map.updateSize();
                               // alert('fin de cargar reclamos');
                            }, 1650);


                        setTimeout(function(){
                            hideLoad();
                            
                            $.mobile.changePage('#pageReclamos',{reverse: true, transition: "slide"});
                            paginaActual="pageReclamos";
                            cleanForm();

                                 
                        }, 1600);
                        

                    },
                    error: function(){
                        hideLoad();
                        showPopup("Error de conexión","No es posible conectarse con el servidor de la aplicación. Revise que su conexión de datos o Wifi se encuentre activa y funcional.",{"Volver a intentar":"btnIntentar","Salir":"btnSalir"});
                        $(".btnIntentar").click(function(){
                            if (enLinea()) {
                                $("#"+paginaActual+" .popupWindow").popup("close");
                                // $("#formIniSesion").submit();
                            };
                        });
                        $(".btnSalir").click(function(){navigator.app.exitApp();});
                    },
                    timeout:15000
                });
            }else{
                showPopup("GPS desactivado","Usted debe activar el GPS para obtener su ubicación.",{"Activar":"btnActivarGPS"});
                $('.btnActivarGPS').click(function(){
                    window.plugins.SettingOpener.Open("ACTION_LOCATION_SOURCE_SETTINGS",function(){
                        $("#"+paginaActual+" .popupWindow").popup("close");
                    },function(e){
                        alert("error: "+e);
                    });
                });
            }
        },function(error){
            console.error("The following error occurred: "+error);
        });
    })
});