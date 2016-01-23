// GLOBALS
var CLEANFORM;
var RESULTS;


// FUNCTIONS

// reset modal contents
function cleanModal(){
  $('#modal-edit [name=form-contents]').replaceWith( CLEANFORM.clone( true, true ) );
}

// EDIT RECORD
function editRecord( button, id ){
  button = $(this);
  // get single record by id
  var confirmation;
  $.when( getRecords( id ) )
   .then(function(){
    // populate modal form fields with values from existing record
    var modal = $('#modal-edit');
    if( RESULTS ){
      $.each(RESULTS, function( i, record ){
        $.each(record, function( field, value ){
          modal.find('[name="'+field+'"]').each(function(){
            var input = $(this);
            if( input.is(':checkbox') ){
              if( parseInt( value ) )
                input.prop('checked', true);
            }
            else{
              input.val( value ).change();
              if( input.is('select') )
                input.material_select(); // re-initialize material select
            }
            if( input.val() )
              input.parents('.row').eq(0).removeClass('hide');
          });
        });
      });

      modal.openModal();
      modal.find('input:visible:first').focus();
    }
  });
}

// GET EXISTING RECORDS
function getRecords( id ){
  var table = $('[name=table-records]');
  var params = {action: 'get', dataType: 'json', table: 'records', orderBy: 'r.id DESC'};
  if( id ) params.where = {id: id};
  var done = $.ajax({
    type: 'POST',
    data: params,
    dataType: 'json',
    beforeSend: function(){
      if( !id )
        table.before('<div class="progress"><div class="indeterminate"></div></div>');
    },
    complete: function(){
      $(document).find('.progress').remove();
    },
    success: function( output ){
      if( output.results && output.results.length ){
        if( id ){
          RESULTS = output.results;
        }
        else{
          var rows = '';
          var templateSource = $('#table-row-record').html();
          $.each( output.results, function(i, result){
            var template = Handlebars.compile( templateSource );
            $.each( result, function( key, value ){
              if( !$.isNumeric( key ) ){
                if( key === 'dependent' ) value = ( parseInt(value) ) ? 'Yes' : 'No';
                value = ( !value ) ? '-' : value;
                result[key] = value;
              }
              rowHTML = template( result );
            });
            rows += rowHTML;
          });
          table.find('tbody').html( rows ).parents('.card').eq(0).removeClass('hide');
        }
      }
      else{
        table.find('tbody').html('').parents('.card').eq(0).addClass('hide');
      }

      // display status and message as 'Toast'
        if( output.message ){
          var textColor = ( output['text-color'] ) ? output['text-color']+'-text text-lighten-2' : '';
          Materialize.toast('<span class="'+textColor+'">'+output.message+'</span>', 5000);
        }

      // display any messages for the console
      if( output.console )
        console.log( output.console );

      // modal triggers
      $(document).find('.modal-trigger').leanModal({
        complete: cleanModal
      });
    }
  });
  return done;
}

// REMOVE EXISTING RECORD
function removeRecord( button, id ){
  // check for id AND confirm user really wants to delete record
  if( id && confirm("Are you sure you want to permanently remove this record?") ){
    button = $(button);
    $.ajax({
      type: 'POST',
      data: {action: 'delete', dataType: 'json', id: id},
      dataType: 'json',
      beforeSend: function(){
        button.addClass('fa-spinner fa-spin').removeClass('fa-trash');
      },
      complete: function(){
        button.addClass('fa-trash').removeClass('fa-spinner fa-spin');
      },
      success: function( confirmation ){
        // check if successful
        if( confirmation.status === 'success' ){
          // remove table row
          button.parents('tr').eq(0).remove();
          getRecords();
        }

        // display status and message as 'Toast'
        if( confirmation.message ){
          var textColor = ( confirmation['text-color'] ) ? confirmation['text-color']+'-text text-lighten-2' : '';
          Materialize.toast('<span class="'+textColor+'">'+confirmation.message+'</span>', 5000);
        }

        // display any messages for the console
        if( confirmation.console )
          console.log( confirmation.console );
      }
    });
  }
}

// INITIALIZE PARALLAX
function initParallax(){
  var parallaxContainer = $('.parallax-container');
  if( parallaxContainer.is(":visible") ){
    var height = window.innerHeight * 0.75;
    parallaxContainer
      .css('height', height + "px")
      .next('.row').eq(0).css('margin-top', (-(height) / 3 ) + "px" );
    $('.parallax').parallax();
  }
}


// LISTENERS

$(document)
.ready(function(){
  // save clean copy/clone of form contents for easy resetting of form fields
  CLEANFORM = $('[name=form-contents]').clone( true, true );

  // inititialize datepicker
  $('.datepicker').pickadate({
    selectMonths: true, // Creates a dropdown to control month
    selectYears: 15, // Creates a dropdown of 15 years to control year
    format: "yyyy-mm-dd",
    closeOnSelect: true,
    min: '1900-01-01',
    // container: 'body'
  });

  // inititialize select elements
  $('select').material_select();


  // initialize and resize parallax to window size
  initParallax();

  // init records
  getRecords();
});


// FORM SUBMIT
$('form').submit(function( e ){
  e.preventDefault();
  var form = $(this);
  var isGood = true;
  form.find(':required').each(function( i, input ){
    input = $(input);
    if( input.val() && $.trim( input.val() ).length === 0 )
      isGood = false;
  });

  if( isGood ){
    $.ajax({
      type: 'POST',
      data: form.serialize() + "&dataType=json&action=insert",
      dataType: 'json',
      beforeSend: function(){
        form.find('.preloader-wrapper').removeClass('hide');
      },
      complete: function(){
        form.find('.preloader-wrapper').addClass('hide');
        // close any modals
        $('.modal').closeModal();
      },
      success: function( confirmation ){
        form.find('.preloader-wrapper').addClass('hide');
        // display status and message as 'Toast'
        if( confirmation.message ){
          var textColor = ( confirmation['text-color'] ) ? confirmation['text-color']+'-text text-lighten-2' : '';
          Materialize.toast('<span class="'+textColor+'">'+confirmation.message+'</span>', 5000);
        }

        // if successful, reload records
        if( confirmation.status === 'success' )
          getRecords();
        // display any messages for the console
        if( confirmation.console )
          console.log( confirmation.console );
      }
    });
  }
});

// USA selection
// If USA is selected, then require state selection
$('[name=primary_destination]').change(function(){
  var input      = $(this);
  var stateField = input.parents('form').eq(0).find('[name=state_code]');

  if( input.val() === 'US' ){
    stateField.attr('disabled', false).attr('required', true).parents('.row').eq(0).removeClass('hide');
    stateField.focus();
  }
  else{
    stateField.attr('disabled', true).attr('required', false).parents('.row').eq(0).addClass('hide');
  }
});
// if state input has value, then be sure the input is enabled
// used mainly for cases when value is changed by JS
$('[name=state_code]').change(function(){
  var input = $(this);
  if( input.val() )
    input.attr('disabled', false);
});


// re-initialize and size parallax on window resize
$(window).resize( initParallax );
