(function( $ ) {
"use strict";

	$('.imue .nav-tab-wrapper a').on('click',function(e){
		
		e.preventDefault();
		
		if($(this).hasClass("prem") ){
		}else{
			
			
			var url = $(this).attr("href");
			$('.imue').addClass('loading');
			$("body").load($(this).attr("href"),function(){
				window.history.replaceState("object or string", "Title", url );
			});				
		}

		
	});	
	

	$('.imue #upload').attr('disabled','disabled');
    $(".imue .imueFile").on('change',function () {
        var wpeifileExtension = ['xls', 'xlsx'];
        if ($.inArray($(this).val().split('.').pop().toLowerCase(), wpeifileExtension) === -1) {
            alert("Only format allowed: "+wpeifileExtension.join(', '));	
			$(".imue input[type='submit']").attr('disabled','disabled');
        }else{
			$(".imue input[type='submit']").removeAttr('disabled');
			$(".imue").find('form').submit();
		}
    });
	
	$(".imue #user_import").on("submit", function (e) {
		e.preventDefault();
				var wpeiData = new FormData();
				$.each($('.imueFile')[0].files, function(i, file) {
					wpeiData.append('file', file);
				});	
				wpeiData.append('_wpnonce',$("#_wpnonce").val());
				wpeiData.append('importUsers',$("#importUsers").val() );
				var url= window.location.href;
				$.ajax({
					url: window.location.href,
					data: wpeiData,
					cache: false,
					contentType: false,
					processData: false,
					type: 'POST',
					beforeSend: function() {	
						$("html, body").animate({ scrollTop: 0 }, "slow");
						$('.imue').addClass('loading');	
					},					
					success: function(response){
						$(".imue .result").slideDown().html($(response).find(".result").html());
						$('.imue').removeClass('loading');	
						$("#user_import").fadeOut();	
						imueDragDrop();	
						premium();
						
						$(".imue #user_process").on('submit',function(e) {
							e.preventDefault();
							if($("input[name='email']").val() !='' && $("input[name='username']").val() !='' ){								
								$(".progressText").fadeIn();
								var total = $(".imue input[name='finalupload']").val() ;
								$(".imue .total").html(total-1);
								var i = 2;	
								$('.imue').addClass('loading');
								
								function imueImportUsers() {									
									var start = parseInt($(".imue input[name='start']").val() ,10 );
									var total = parseInt( $(".imue input[name='finalupload']").val(),10 ) ;
									if(start > total  ){
										$('.imue .success , .imue .error, .imue .warning').delay(2000).hide();
										$(".imue #user_import").delay(5000).slideDown();
									}else{	
										
										$.ajax({
											url: imue.ajax_url,
											data: $(".imue #user_process").serialize(),
											type: 'POST',
											beforeSend: function() {
												$("html, body").animate({ scrollTop: 0 }, "slow");												
												$(".imue #user_process").hide();
											},						
											success: function(response){
												$(".imue .importMessage").slideDown().html($(response).find(".importMessage").html());
												$(".imue .ajaxResponse").html(response);
												$(".imue .thisNum").html($("#AjaxNumber").html() );
												
													$(".imue input[name='start']").val(i + 1 );
													i++;
													
											},complete: function(response){
													$('.imue').removeClass('loading');
													imueImportUsers();
											}
										});	
									
									}
								}
								
								imueImportUsers();
							}else alert('Email & Username are Mandatory.');
							
						});							
					}
			});			
	});	

			//drag and drop
			function imueDragDrop(){
				$('.imue .draggable').draggable({cancel:false});
				$( ".imue .droppable" ).droppable({
				  drop: function( event, ui ) {
					$( this ).addClass( "ui-state-highlight" ).val( $( ".ui-draggable-dragging" ).val() );
					$( this ).attr('value',$( ".ui-draggable-dragging" ).attr('key')); //ADDITION VALUE INSTEAD OF KEY
					$( this ).val($( ".ui-draggable-dragging" ).attr('key') ); //ADDITION VALUE INSTEAD OF KEY					
					$( this ).attr('placeholder',$( ".ui-draggable-dragging" ).attr('value')); 				
					$( ".ui-draggable-dragging" ).css('visibility','hidden'); //ADDITION + LINE
					$( this ).css('visibility','hidden'); //ADDITION + LINE
					$( this ).parent().css('background','#90EE90');						
					
					if($("input[name='ID']").hasClass('ui-state-highlight') ){
						$(".hideOnUpdateById").hide();
					}
					
				  }		 
				});		
			}
			imueDragDrop();
			

			$(".imue .exportToggler").on('click',function(){
				$(".imue #exportUsersForm").slideToggle();
				$(".imue .exportTableWrapper").slideToggle();
				$(".imue .downloadToExcel").slideToggle();
			});



			
			$(".imue #exportUsersForm").on('submit',function(e) {
					e.preventDefault();
		
					
				//if checkbox is checked
				$(".imue .fieldsToShow").each(function(){
					if($(this).is(':checked')){
					}else localStorage.setItem($(this).attr('name') ,$(this).attr('name') );
				});	
				
				$.ajax({
					url: $(this).attr('action'),
					data:  $(this).serialize(),
					type: 'POST',
					beforeSend: function() {									
						$('.imue').addClass('loading');		
					},						
					success: function(response){
						
						$('.imue').removeClass('loading');
						
						$(".imue #exportUsersForm").hide();
						
						$(".resultExport").slideDown().html($(response).find(".resultExport").html());
							
								//if checkbox is checked
								$(".imue .fieldsToShow").each(function(){									
									if (localStorage.getItem($(this).attr('name')) ) {
										$(this).attr('checked', false);
									}//else $(this).attr('checked', false);							
									localStorage.removeItem($(this).attr('name'));	
								});	
									
									var i=0;
									$(".imue input[name='total']").val($(".imue .totalPosts").html());
									$(".imue input[name='start']").val($(".imue .startPosts").html());							
									var total = $(".imue input[name='total']").val();	
									var start = $(".imue input[name='start']").val();
									progressBar(start,total) ;

								function wpeiDataExportUsers() {
									var total = $(".imue input[name='total']").val();
									var start = $(".imue input[name='start']").val() * i;
									
									if($(".imue .totalPosts").html()  <=500){
											$(".imue input[name='posts_per_page']").val($(".imue .totalPosts").html() );
									}else $(".imue input[name='posts_per_page']").val($(".imue .startPosts").html());
									
									var dif = total- start;
									
									if( $('#toExport >tbody >tr').length >= total ){
																				
										
										$('.imue #myProgress').delay(10000).hide('loading');

										
										$.getScript(imue.exportfile, function() {									
											
											$("#toExport").tableExport({position: "top"});
											$('.xlsx').trigger('click');										  
										});										

										
										$("body").find('#exportUsersForm').find("input[type='number'],input[type='text'], select, textarea").val('');
										$('.imue .message').html('Job Done!');
										$('.imue .message').addClass('success');
										$('.imue .message').removeClass('error');
									}else{	
									
										var dif = total - start;
										if(total> 500 && dif <=500 ){
											$(".imue  input[name='posts_per_page']").val(dif);
										} 									
										
										$.ajax({
											url: imue.ajax_url,
											data: $(".imue #exportUsersForm").serialize(),
											type: 'POST',
											beforeSend: function() {
												$("html, body").animate({ scrollTop: 0 }, "slow");	
												$('.imue').removeClass('loading');
											},						
											success: function(response){	

												$(".imue .tableExportAjax").append(response);
												i++;
												start = $(".imue input[name='start']").val() * i;
												
												$(".imue  input[name='offset']").val(start);												
												var offset = $(".imue  input[name='offset']").val();													
												console.log("dif "+ dif+" i: "+ i + " offset: " + offset + " start: " + start+ " total: " + total);
												
												progressBar(start,total) ;	
											},complete: function(response){			
													wpeiDataExportUsers();	
											}
										});
									}
								}
								wpeiDataExportUsers();								
					}
					});	
			});	



			function progressBar(start,total) {
				var width = (start/total) * 100;
				var elem = document.getElementById("myBar");   
				if (start >= total-1) {
				  elem.style.width = '100%'; 
				} else {
				  start++; 
				  elem.style.width = width + '%'; 
				}
			}	
	
	function premium(){
		$(".imue .prem").click(function(e){
			e.preventDefault();
			$("#imue_popup").slideDown();
		});

		$("#imue_popup .close").click(function(e){
			e.preventDefault();
			$("#imue_popup").fadeOut();
		});		

		var modal = document.getElementById('imue_popup');

		// When the user clicks anywhere outside of the modal, close it
		window.onclick = function(event) {
			if (event.target == modal) {
				modal.style.display = "none";
			}
		}
	}
	premium();
	
	
		$("#imue_signup").on('submit',function(e){
			e.preventDefault();	
			var dat = $(this).serialize();
			$.ajax({
				
				url:	"https://extend-wp.com/wp-json/signups/v2/post",
				data:  dat,
				type: 'POST',							
				beforeSend: function(data) {								
						console.log(dat);
				},					
				success: function(data){
					alert(data);
				},
				complete: function(data){
					dismissImue();
				}				
			});	
		});
		

		function dismissImue(){
			
				var ajax_options = {
					action: 'imue_push_not',
					data: 'title=1',
					nonce: 'imue_push_not',
					url: imue.ajax_url,
				};			
				
				$.post( imue.ajax_url, ajax_options, function(data) {
					$(".imue_notification").fadeOut();
				});
				
				
		}
		
		$(".imue_notification .dismiss").on('click',function(e){
				dismissImue();
				console.log('clicked');
				
		});	
		
	
		

		//EXTENSIONS
		$(".imue .wp_extensions").click(function(e){
			
			e.preventDefault();
			
			if( $('#imue_extensions_popup').length > 0 ) {
			
				$(".imue .get_ajax #imue_extensions_popup").fadeIn();
				
				$("#imue_extensions_popup .imueclose").click(function(e){
					e.preventDefault();
					$("#imue_extensions_popup").fadeOut();
				});		
				var extensions = document.getElementById('imue_extensions_popup');
				window.onclick = function(event) {
					if (event.target === extensions) {
						extensions.style.display = "none";
						localStorage.setItem('hideIntro', '1');
					}
				}					
			}else{
				

				var action = 'imue_extensions';
				$.ajax({
					type: 'POST',
					url: imue.ajax_url,
					data: { 
						"action": action
					},							
					 beforeSend: function(data) {								
						$("html, body").animate({ scrollTop: 0 }, "slow");
						$('.imue').addClass('loading');
						
					},								
					success: function (response) {
						$('.imue').removeClass('loading');
						if( response !='' ){
							//console.log(response);
							$('.imue .get_ajax' ).css('visibility','hidden');
							$('.imue .get_ajax' ).append( response );
							$('.imue .get_ajax #imue_extensions_popup' ).css('visibility','visible');
							$(".imue .get_ajax #imue_extensions_popup").fadeIn();
							
							$("#imue_extensions_popup .imueclose").click(function(e){
								e.preventDefault();
								$("#imue_extensions_popup").fadeOut();
							});		
							var extensions = document.getElementById('imue_extensions_popup');
							window.onclick = function(event) {
								if (event.target === extensions) {
									extensions.style.display = "none";
									localStorage.setItem('hideIntro', '1');
								}
							}							
						}
					},
					error:function(response){
						console.log('error');
					}
				});			
			}
		});	
		
	
})( jQuery )