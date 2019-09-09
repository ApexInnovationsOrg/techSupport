@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">

			
				<div class="panel-heading">Ticket Info :: {{ $ownerName }}</div>
				<div class="panel-body">
					
					@if (count($errors) > 0)
						<div class="alert alert-danger">
							<strong>Whoops!</strong> There were some problems with your input.<br><br>
							<ul>
								@foreach ($errors->all() as $error)
									<li>{!! $error !!}</li>
								@endforeach
							</ul>
						</div>
					@endif
					<!-- Include stylesheet -->
					<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">				
					<link rel="stylesheet" type="text/css" media="screen,projection,print" href="../../css/jquery.datetimepicker.css">

				
					<div class="form-horizontal">
						<fieldset>
							<legend>{{ $ticket->CodeName }}</legend>
						</fieldset>
						
						<form method="POST" id='EmailReplyForm' action="{{ url('/sendReplyToTicket') }}">
							<div class="form-group">
							  <label class="col-md-4 control-label">Email Message:</label>
							  <div class="col-md-4">
							    {!! nl2br($ticket->EmailMessage) !!}
							  </div>
							</div>
							
							<!-- Create the editor container -->
							<div id="replyMessageContainer" class='notice'>
								<div class="form-group">
								  <label class="col-md-4 control-label">Reply Email:</label>
								  <div class="col-md-8">
									<div id="replyMessage">
								  </div>
								</div>								
							</div>
							
							<hr/>
							<label class="col-md-4 control-label">Reply Options</label>
							<div class="col-md-4">											
								<input type='submit' id="cancelEmail" name='cancelEmail' {{ isset($notOwner) ? 'disabled="disabled"' : '' }} class="btn btn-danger" value='Cancel'></input>
								<input type='submit' id="sendEmail" name='sendEmail' {{ isset($notOwner) ? 'disabled="disabled"' : '' }} class="btn btn-primary" value='Send'></input>
							</div>
							
							<input type="hidden" name="_token" value="{{ csrf_token() }}">
							<input id="ticketKey" name="key" type="hidden" value="{{$ticket->Key}}">
							<input type='hidden' name='formAction' id='formAction' value=''/>
							<input type='hidden' name='formValue' id='formValue' value=''/>
						</form>
					</div>
					
				</div>		
				
				<style>
					.ql-container{
						height: 15em;
					}
				</style>
				<!-- Include the Quill library -->
				<script src="//cdn.quilljs.com/1.3.6/quill.min.js"></script>
				<script>
				  //Init quill
				  var quill = new Quill('#replyMessage', {
					modules: {
						toolbar: [
							  ['bold', 'italic', 'underline']
							]
					},
					placeholder: 'Reply to the Contact Us Email...\n\n\n\n\n\n\n\nSignature is automatically added to your email reply',
					theme: 'snow'
				  });
				
				  //When form submitted, grab contents from quill and selector			  
				  $('#cancelEmail').on('click',function(e){
					  $('#formAction').val('cancel');
					  formSubmission();
				  });
				  
				  
				  $('#sendEmail').on('click',function(e){					 
				      $('#formAction').val('send');
					  $('#formValue').val(quillGetHTML(quill.getContents()));	
					  formSubmission();
				  });
				  
				  function formSubmission(){
					  $('#EmailReplyForm').submit();
				  }
				  
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