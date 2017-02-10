/*
// Below code for Changing th Course Curriculum into Accordion style
 jQuery(document).ready(function(){
   jQuery('.course_curriculum .course_section').click(function(event){
   		jQuery(this).toggleClass('show');
   		jQuery(this).nextUntil('.course_section').toggleClass('show');
   });
   jQuery('.course_timeline .section').click(function(event){
   		jQuery(this).toggleClass('show');
   		jQuery(this).nextUntil('.section').toggleClass('show');
   });
}); */



/*jQuery(document).ready(function($){
   $('div#headertop ul.topmenu li a.vbpregister').click(function(event){
    event.preventDefault();
  $('body').find('.smallimg.vbplogin').trigger('click');
   });
 });
*/

/*jQuery(document).ready(function($){
   $('.login_trig').click(function(){
    if(!$('body').find('div#global.global').hasClass('login_open')){
      $('body').find('a#login_trigger').trigger('click');
      $('body').find('div#global.global').addClass('login_open');
      $('div#global.global').trigger('login_open');
    }else{
      $('body').find('a#login_trigger').trigger('click');
      $('body').find('div#global.global').removeClass('login_open');
      $('div#global.global').trigger('login_open');
    }
    
   });

});*/


  
  



jQuery(document).ready(function($){
var i = 0;
jQuery('.stats_content ol.marks li').each(function(){
  i++;
  var $this = jQuery(this);
  $this.prepend(' '+i+'.');
});

  jQuery('.unit_content').on('unit_traverse',function(){ 
    $('video').each(function(){ 
          player = new MediaElementPlayer('video');
        $('video').on("play", function() {
            player.enterFullScreen();
        });
    });
 
  });
});
 jQuery(document).ready(function(){



  jQuery('body').find('.show_explaination.tip').trigger('click');



 		  setTimeout(function(){var titleheight=jQuery('section#title').outerHeight(true);jQuery('<style>section#title:after {height: ' + titleheight + 'px;}</style>').appendTo('head')},2000);

 		jQuery('.login_trig').click(function(){
 				jQuery('body').find('#login_trigger').trigger('click');
 		});
 		jQuery('i.icon-task').parent().parent().css("background-color", "yellow");

		jQuery('.ajax_unit').each(function(){
			console.log('%');
			jQuery(this).on('click',function(event){
				event.preventDefault();
			jQuery(this).magnificPopup({
					type: 'ajax',
					alignTop: true,
					fixedContentPos: true,
					fixedBgPos: true,
					overflowY: 'auto',
					closeBtnInside: true,
					preloader: false,
					midClick: true,
					removalDelay: 300,
					mainClass: 'my-mfp-zoom-in',
					callbacks: {
					
						parseAjax: function(mfpResponse) {
							
							mfpResponse.data = jQuery(mfpResponse.data).find('.unit_wrap');

						},

						ajaxContentAdded: function() {

							jQuery('video,audio').mediaelementplayer();

							jQuery('.fitvids').fitVids();

							jQuery('.tip').tooltip();

							jQuery('.nav-tabs li:first a').tab('show');

							jQuery('.nav-tabs li a').click(function(event){

							event.preventDefault();

							jQuery(this).tab('show');

							});

						}

					}
				});
			});
		});

 });



jQuery(document).ready(function($){
  
$('.question').each(function(){
      var $this = $(this);
      jQuery('.question_options.sort').each(function(){
        jQuery(this).sortable({
          revert: true,
          cursor: 'move',
          refreshPositions: true, 
          opacity: 0.6,
          scroll:true,
          containment: 'parent',
          placeholder: 'placeholder',
          tolerance: 'pointer',
        }).disableSelection();
    });
    //Fill in the Blank Live EDIT
    $(".live-edit").liveEdit({
        afterSaveAll: function(params) {
          return false;
        }
    });

    //Match question type
    $('.question_options.match').droppable({
      drop: function( event, ui ){
      $(ui.draggable).removeAttr('style');
      $( this )
            .addClass( "ui-state-highlight" )
            .append($(ui.draggable))
      }
    });
    $('.question_options.match li').draggable({
      revert: "invalid",
      containment:'#question'
    });
    $( ".matchgrid_options li" ).droppable({
        activeClass: "ui-state-default",
        hoverClass: "ui-state-hover",
        drop: function( event, ui ){
          childCount = $(this).find('li').length;
          $(ui.draggable).removeAttr('style');
          if (childCount !=0){
              return;
          }  
          
           $( this )
              .addClass( "ui-state-highlight" )
              .append($(ui.draggable))
        }
      });
  });

$('body').find('.check_answer').click(function(){
 var $this = $(this);

          $this.find('.message').remove();
          var id = $(this).attr('data-id');
          var answers = eval('ans_json'+id);
          
          switch(answers['type']){
            case 'select':
            case 'truefalse':
            case 'single':
            case 'sort':
            case 'match':
              value ='';
              if($this.find('input[type="radio"]:checked').length){
                value = $this.find('input[type="radio"]:checked').val();
              }
              if($this.find('select').length){
                value = $this.find('option:selected').val();
              }

              $this.find('.question_options.sort li.sort_option').each(function(){
                var id = $(this).attr('id');
                if( jQuery.isNumeric(id))
                  value +=id+',';
              });

              $this.find('.matchgrid_options li.match_option').each(function(){
                var id = $(this).attr('id');
                if( jQuery.isNumeric(id))
                  value +=id+',';
              });

              if(answers['type'] == 'sort' || answers['type'] == 'match'){
                value = value.slice(0,-1);
              }
       
                if( value == answers['answer']){
                  $this.append('<div class="message success">'+vibe_course_module_strings.correct+'</div>');
                }else{
                  $this.append('<div class="message error">'+vibe_course_module_strings.incorrect+'</div>');
                }
              
            break;
            case 'multiple':
              if($this.find('input[type="checkbox"]:checked').length){
                  if($this.find('input[type="checkbox"]:checked').length == answers['answer'].length){
                    $this.find('input[type="checkbox"]:checked').each(function(){
                      if ($.inArray($(this).val(), answers['answer']) == -1){
                        $this.append('<div class="message error">'+vibe_course_module_strings.incorrect+'</div>');
                        return false;  
                      }
                    });
                    $this.append('<div class="message success">'+vibe_course_module_strings.correct+'</div>');
                  }else{
                    $this.append('<div class="message error">'+vibe_course_module_strings.incorrect+'</div>');
                  }                  
              }
            break;

          }
      });
    });


jQuery(document).ready(function ($) {
/*$('.unit_content').on('unit_traverse',function(){
$('body').find('a.unit_button.submit_inquiz').on('click',function(){
  alert('asda');
$('html,body').animate({scrollTop: 0}, 800);
});
});*/
/*$('body').find('a.unit_button.submit_inquiz').on('click',function(){
  alert('asda');
$('html,body').animate({scrollTop: 0}, 800);
});*/
    function prompt_the_user(unit_id,unit_name){
       console.log(unit_name);
       console.log(unit_id);
        jQuery('input.review_course.unit_button.full.button[name=submit_course]').text(unit_name);
        //this has to be moved somewhwre according to logic 
        jQuery('input.review_course.unit_button.full.button[name=submit_course]').removeClass('disabled');
    }
    jQuery('input.review_course.unit_button.full.button[name=submit_course]').addClass('disabled');
    jQuery('input.review_course.unit_button.full.button[name=submit_course]').on('click',function(e){
      var $this = jQuery(this);
      var flag=0;
      var unit_name='';
      var unit_id='';
      if($this.hasClass('disabled'))
      e.preventDefault();

      jQuery('.course_timeline li.unit_line').each(function(){
      var $this_li = jQuery(this);

      if(!($this_li.hasClass('done'))){
       flag=1;
       unit_id=$this_li.children('a.unit').attr('data-unit');
       unit_name=$this_li.children('a.unit').text();
      
       prompt_the_user(unit_id,unit_name);
       return false;
      }
     

      });
    });


});






