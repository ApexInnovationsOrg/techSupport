@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<h3>Notice Editor</h3>
				<hr/>
				@if(Session::has('message'))
					<div class="alert alert-success">
						<h2>{{ Session::get('message') }}</h2>
					</div>
				@endif
				
				@if (count($errors) > 0)
					<div class="alert alert-danger">
						<strong>Whoops!</strong> There were some problems with your input.<br><br>
						<ul>
							@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
				@endif
				
				@if (count($notices) > 0)
					<div class="notice">
						<label for='noticeSelect'><strong>Select Notice:&nbsp;&nbsp;</strong></label>
						<select name='noticeSelect' id='noticeSelect'>
							<option value='0'>&lt; &lt; Create New Notice &gt; &gt;</option>
						</select>
					</div>
					<hr/>
				@endif
				
				@if(Session::has('selection'))
					<script>
						var selection = {{ Session::get('selection') }};
					</script>
				@endif
				
				<!-- Include stylesheet -->
				<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">				
				<link rel="stylesheet" type="text/css" media="screen,projection,print" href="../../css/jquery.datetimepicker.css">

				<!-- Create the editor container -->
				<div id="noticeContainer" class='notice'>
					<strong>Notice:</strong>
					<div id="noticeEditor">
					</div>
				</div>
				<hr/>
				<form action="{{ url('noticeEditorSaveAndDelete') }}" id='submitEditorForm' class='notice' method='POST'>
					<input type='hidden' name='noticeID' id='noticeID' value=''/>
					<input type='hidden' name='_token' value="{{ csrf_token() }}">
					<input type='hidden' name='formValue' id='formValue' value=''/>
					<input type='hidden' name='formAction' id='formAction' value=''/>
					<div>
						<div class='noticeForm'>
							<label for='noticeStartDate'><strong>Start Date:</strong>&nbsp;&nbsp;</label><input name='noticeStartDate' id='noticeStartDate' class='datePicker' placeholder='-Not Required-' autocomplete="off" />
						</div>
						<div class='noticeForm'>
							<label for='noticeEndDate'><strong>End Date:</strong>&nbsp;&nbsp;</label><input name='noticeEndDate' id='noticeEndDate' class='datePicker' placeholder='-Not Required-' autocomplete="off" />		
						</div>
						<div class='noticeForm'>						
							<label for='noticeActive'><strong>Active:</strong>&nbsp;&nbsp;</label><input type='checkbox' name='noticeActive' id='noticeActive' class='' />
						</div>
					</div>
					<div id='noticeSubmit'>							
						<input type='submit' name='deleteButton' value='Delete' id='deleteButton' class='hidden btn btn-danger'></input>
						<input type='submit' name='submitButton' value='Create' id='submitButton' class='btn btn-success'></input>
					</div>					
				</form>
				
				<!-- Include the Quill library -->
				<script src="//cdn.quilljs.com/1.3.6/quill.min.js"></script>				
				<script type="text/javascript" src="../../scripts/jquery.datetimepicker.js"></script>

				<!-- Initialize Quill editor -->
				<script>
					
				  //Init quill
				  var quill = new Quill('#noticeEditor', {
					modules: {
						toolbar: [
							  [{ header: [1, 2, false] }],
							  ['bold', 'italic', 'underline']
							]
					},
					placeholder: 'Create a tech support notice...',
					theme: 'snow'
				  });
				  
				  //Grab notices that were passed to page
				  var notices = JSON.parse({!! json_encode($notices) !!});		
				  
				  //Generate selection list
				  var count = 1;
				  notices.forEach(function(value){
					  var htmlText = quillGetHTML(JSON.parse(value.notice));
					  htmlText = htmlText.substring(0,htmlText.length - 4);
					  
					  var selected = '';
					  
					  if (typeof selection !== 'undefined') {
						if(selection == value.id){
							selected = "selected='selected'";
						}
					  }						  
						
					  $('#noticeSelect').append("<option data-id='" + value.id + "' value='" + count + "'" + selected + ">" + count + ". " + htmlText.substring(0,35) + "...</option>");
					  count++;
				  });
				  	
			      //When selection changes, fill in all fields
				  $('#noticeSelect').on('change',function(){
					  if($('#noticeSelect').val() == 0){
						quill.setContents('');
						$('#noticeStartDate').val('');
						$('#noticeEndDate').val('');
						$('#noticeActive').prop('checked',false);
						$('#submitButton').val('Create');
						$('#deleteButton').addClass('hidden');
					  }else{
						var noticeID = $('#noticeSelect').val() - 1;
						quill.setContents(JSON.parse(notices[noticeID].notice));
						$('#noticeStartDate').val(notices[noticeID].startDate);
						$('#noticeEndDate').val(notices[noticeID].endDate);
						$('#noticeActive').prop('checked',notices[noticeID].active);
						$('#submitButton').val('Update');
						$('#deleteButton').removeClass('hidden');
					  }					  
				  });
				  
				  //When form submitted, grab contents from quill and selector			  
				  $('#deleteButton').on('click',function(e){
					  $('#formAction').val('delete');
					  formSubmission();
				  });
				  
				  
				  $('#submitButton').on('click',function(e){					 
				      $('#formAction').val('save');
					  formSubmission();
				  });
				  
				  function formSubmission(){
					  $('#formValue').val(JSON.stringify(quill.getContents()));	
					  $('#noticeID').val($('#noticeSelect').find(':selected').data('id'));
					  $('#submitEditorForm').submit();
				  }
				  
				$('.datePicker').datetimepicker({
					format:'Y-m-d H:i'
				});
				
				//When page loads, create our selections
				$(document).ready(function() {
					if($('#noticeSelect').val() != 0){ //value passed in from redirect
						$('#noticeSelect').change();
					}
				});
				
				//Convert quill delta into text for selection preview
				function quillGetHTML(inputDelta) {
					var tempCont = document.createElement("div");
					(new Quill(tempCont)).setContents(inputDelta);
					var text = tempCont.getElementsByClassName("ql-editor")[0].innerHTML;
					return text;
				}
				
				</script>
				
			</div>
		</div>
	</div>
</div>

@endsection
