@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading">Ticket Info :: {{ $ownerName }}</div>
				<div class="panel-body">
					<!-- Unclaim Modal -->
					<div class="modal fade" id="tauntModal" tabindex="-1" role="dialog" aria-labelledby="tauntModal" aria-hidden="true">
					  <div class="modal-dialog">
					    <div class="modal-content">
					      <div class="modal-header">
					        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					        <h4 class="modal-title" id="myModalLabel">Taunt</h4>
					      </div>
					      <form method="POST" action="{{ url('/taunt') }}">
					      <div class="modal-body">
					       <!-- Textarea -->
							<div class="form-group">
							  <label class="col-md-4 control-label" for="taunt" >Taunt:</label>
							  <div class="col-lg-8">                     
							    <textarea class="form-control" id="taunt" placeholder="You suck"  required name="taunt"></textarea>
							  </div>
							</div>
							<div class="row">
								<input type="hidden" name="_token" value="{{ csrf_token() }}">
						  		<input id="key" name="key" type="hidden" value="{{$ticket->Key}}">
						  	</div>
					      </div>
					      <div class="modal-footer">
					        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					        <button type="submit" class="btn btn-primary">Send taunt</button>
					      </div>
					     </form>
					    </div>
					  </div>
					</div>

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
					<div class="form-horizontal">
						<fieldset>
							@if($ticket->Completed == null || isset($_GET['details']))
							<!-- Form Name -->
							<legend>{{ $ticket->CodeName }}</legend>
							@if($ticket->PhoneNumber !== '')
							<div class="form-group">
							  <label class="col-md-4 control-label">Listed Phone Number:</label>
							  <div class="col-md-4 top-buffer">
							    {{ $ticket->PhoneNumber }}
							  </div>
							</div>
							@endif
							<div class="form-group">
								<label class="col-md-4 control-label">The office phone number:</label>
								<div class="col-md-4 top-buffer">
									<a href="tel:3372164599">(337) 216-4599</a>&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;
									<a href="tel:3372164599,,,9,9988#">Callbridge</a>
								</div>
							</div>
							@if($ticket->formattedUserName !== null)
							<div class="form-group">
							  <label class="col-md-4 control-label">User:</label>
							  <div class="col-md-4 top-buffer">
							    <a href={{ url("../EditUsers.php?ID=$ticket->UserID")}} target="_blank">{{ $ticket->formattedUserName }}</a>
							  </div>
							</div>
							@endif
							<div class="form-group">
							  <label class="col-md-4 control-label">Email Message:</label>
							  <div class="col-md-4">
							    {!! nl2br($ticket->EmailMessage) !!}
							  </div>
							</div>
							@if(strpos($ticket->VoicemailFileName,'.wav') !== false)
							<div class="form-group">
							  <label class="col-md-4 control-label" for="textarea" placeholder="Notes...">Voicemail Left:</label>
							  <div class="col-md-4">                     
							    <audio controls style="width:100%">
								  <source src={{ url("../voicemails/$ticket->VoicemailFileName") }} type="audio/wav">
								Your browser does not support the audio element.
								</audio>
							  </div>
							</div>
							@endif
							<!-- Textarea -->
							<div class="form-group">
							  <label class="col-md-4 control-label" for="textarea" placeholder="Notes...">Ticket Notes:</label>
							  <div class="col-md-4">                     
							    <textarea class="form-control" id="textarea" name="textarea">{{ $ticket->Notes }}</textarea>
							  </div>
							</div>
							<input id="ticketKey" type="hidden" value="{{$ticket->Key}}">

							<!-- Button -->
							<div class="form-group">
							  <label class="col-md-4 control-label" for="singlebutton"></label>
							  <div class="col-md-4">
							    <button id="updateNotes" {{ isset($notOwner) ? 'disabled="disabled"' : '' }} name="singlebutton" class="btn btn-primary">Update Notes</button>
							  </div>
							</div>
														<!-- transfer list -->
							<div class="form-group">
							  <label class="col-md-4 control-label" for="textarea" placeholder="Transfers">Transfers</label>
							  <div class="col-md-4">                     
							    <ul class="top-buffer">
							    	@if(count($transferHistory) > 0)
								    	@foreach($transferHistory as $transfer)
								    		@if($transfer->TransferTo !== null)
								    			<li class="top-buffer" data-toggle="tooltip" data-placement="top" title="{{ $transfer->TransferReason }}">{{ $transfer->created_at . ' from ' . $transfer->TransferFrom .  ' to ' . $transfer->TransferTo }}</li>
								    		@else
								    			<li class="top-buffer" data-toggle="tooltip" data-placement="top" title="{{ $transfer->TransferReason }}">{{ $transfer->created_at . ' ' . $transfer->TransferFrom .  ' unclaimed the ticket.'}}</li>
								    		@endif
								    	@endforeach
								    @else
								    	<li>No Transfers</li>
								    	{{-- <li>2015/5/6 from Mike Rubio to John Klein</li>
							    	<li>2015/5/6 from John Klein to Eddie Muller</li> --}}
								    @endif
							    </ul>
							  </div>
							</div>
								<!-- Transfer Modal -->
								<div class="modal fade" id="transferModal" tabindex="-1" role="dialog" aria-labelledby="transferModal" aria-hidden="true">
								  <div class="modal-dialog">
								    <div class="modal-content">
								      <div class="modal-header">
								        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								        <h4 class="modal-title" id="myModalLabel">Transfer Ticket</h4>
								      </div>
								      <div class="modal-body">
										<form method="POST" action="{{ url('/transferTicket') }}">
									        <!-- Textarea -->
											<div class="form-group">
											  <label class="col-md-4 control-label" for="transferReason" >Reason for transferring:</label>
											  <div class="col-lg-8">                     
											    <textarea class="form-control" id="transferReason" placeholder="Your reason"  required name="transferReason"></textarea>
											  </div>
											</div>
											<!-- Select Basic -->
											<div class="form-group">
												  <label class="col-md-4 control-label" for="transferID">Transfer to:</label>
												  <div class="col-md-5">
												    <select id="transferID" name="transferID" required class="form-control">
		    											<option selected disabled hidden value=''>Select Employee</option>
														@foreach($techSupportEmployees as $techSupportEmployee)
															<option {{old('transferID') == $techSupportEmployee->ID ? 'selected' : ''}} value="{{$techSupportEmployee->ID}}">{{ $techSupportEmployee->FirstName . ' ' . $techSupportEmployee->LastName }}</option>
														@endforeach								     
												    </select>
												  </div>
												</div>

											  		<input type="hidden" name="_token" value="{{ csrf_token() }}">
											  		<input id="key" name="key" type="hidden" value="{{$ticket->Key}}">
											    	
										    </div>
										    <div class="modal-footer">
										      <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
										      <button type="submit" class="btn btn-primary">Save changes</button>
										    </div>
								    	</form>
								    </div>
								  </div>
								</div>

								<!-- Unclaim Modal -->
								<div class="modal fade" id="unclaimModal" tabindex="-1" role="dialog" aria-labelledby="unclaimModal" aria-hidden="true">
								  <div class="modal-dialog">
								    <div class="modal-content">
								      <div class="modal-header">
								        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
								        <h4 class="modal-title" id="myModalLabel">Unclaim Ticket</h4>
								      </div>
								      <form method="POST" action="{{ url('/unclaimTicket') }}">
								      <div class="modal-body">
								       <!-- Textarea -->
										<div class="form-group">
										  <label class="col-md-4 control-label" for="unclaimReason" >Reason for unclaiming:</label>
										  <div class="col-lg-8">                     
										    <textarea class="form-control" id="unclaimReason" placeholder="Your reason"  required name="unclaimReason"></textarea>
										  </div>
										</div>
											<input type="hidden" name="_token" value="{{ csrf_token() }}">
									  		<input id="key" name="key" type="hidden" value="{{$ticket->Key}}">
								      </div>
								      <div class="modal-footer">
								        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
								        <button type="submit" class="btn btn-primary">Save changes</button>
								      </div>
								     </form>
								    </div>
								  </div>
								</div>


							<!-- Button (Double) -->
							<div class="form-group">
							  <label class="col-md-4 control-label" for="button1id"></label>
							  <div class="col-md-8">
							    <button id="button1id" name="button1id" class="btn btn-warning" data-toggle="modal" data-target="#transferModal"  {{ isset($notOwner) || isset($ticket->Completed) ? 'disabled="disabled"' : '' }}>Transfer Ticket</button>
							    <button id="Unclaim Ticket" name="Unclaim Ticket" class="btn btn-danger" data-toggle="modal" data-target="#unclaimModal"  {{ isset($notOwner) || isset($ticket->Completed) ? 'disabled="disabled"' : '' }}>Unclaim Ticket</button>
							  </div>
							</div>
							<hr/>
							<!-- Button -->
							<div class="form-group">
							  <label class="col-md-4 control-label" for="singlebutton">Ticket Resolved</label>
							  <div class="col-md-4">
							  	<form method="POST" action="{{ url('/completedTicket') }}">
							  		<input type="hidden" name="_token" value="{{ csrf_token() }}">
							  		<input id="key" name="key" type="hidden" value="{{$ticket->Key}}">
							    	<button id="singlebutton" name="singlebutton" {{ isset($notOwner) || isset($ticket->Completed) ? 'disabled="disabled"' : '' }} class="btn btn-success">Completed</button>
							    </form>
							  </div>
							</div>
							<script>
								

									$('#updateNotes').click(function(){
										$.ajax({
										method: "POST",
										  url: "updateNotes",
										  data: {	
										  "_token":"{{ csrf_token() }}",
										  	'note':$('#textarea').val(),
										  	'key':$('#ticketKey').val()
										  }	
										}).done(function() {
										 	alert('saved');
										});
									});

									 $('[data-toggle="tooltip"]').tooltip();
								
							</script>
							@elseif($ticket->BountyClaimed == 'N')
								<form class="form-horizontal" method="POST" action="{{ url('/claimBounty') }}">
								<fieldset>
									<input type="hidden" name="_token" value="{{ csrf_token() }}">
							  		<input id="key" name="key" type="hidden" value="{{$ticket->Key}}">
							  		<input id="userID" type="hidden" name="userID" value="{{ old('userID') }}">
								<!-- Form Name -->
								<legend>Nice job! Claim your bounty for <strong>{{ $ticket->CodeName }}</strong></legend>

								<!-- Search input-->
								<div class="form-group">
								  <label class="col-md-4 control-label" for="userSearch" ><img id="loader" class="loader" src="images/ui-anim_basic_16x16.gif" />&nbsp;&nbsp;User</label>
								  <div class="col-md-8">
								    <input id="userSearch" class="userSearch" name="userSearch" type="search" placeholder="User Name" class="typeahead tt-query form-control input-md" autocomplete="off" value="{{ old('userSearch')}}" >
								    <p class="help-block">User that was assisted. <span id="selectedUserId">{{ old('userID') }}</span></p>
								  </div>
								</div>

								<!-- Select Basic -->
								<div class="form-group">
								  <label class="col-md-4 control-label" for="resolutionSelect">Issue Resolution</label>
								  <div class="col-md-6">
								    <select id="resolutionSelect" name="resolutionSelect" class="form-control">
										<option selected disabled hidden value=''>Action Taken</option>
										@foreach($supportTypes as $supportType)
											<option {{old('resolutionSelect') == $supportType->ID ? 'selected' : ''}} value="{{$supportType->ID}}">{{ $supportType->Name }}</option>
										@endforeach
								    </select>
								  </div>
								</div>

								<!-- Textarea -->
								<div class="form-group">
								  <label class="col-md-4 control-label" for="resolutionNotes">Resolution Notes</label>
								  <div class="col-md-4">                     
								    <textarea class="form-control" id="resolutionNotes" name="resolutionNotes" placeholder="Notes...">{{ old('resolutionNotes') }}</textarea>
								  </div>
								</div>

								<!-- Multiple Radios (inline) -->
								<div class="form-group">
								  <label class="col-md-4 control-label" for="solvedRadio">Resolved?</label>
								  <div class="col-md-4"> 
								    <label class="radio-inline" for="solvedRadio-0">
								      <input type="radio" {{old('solvedRadio') == "Y" ? 'checked="checked"' : ''}} name="solvedRadio" id="solvedRadio-0" value="Y">
								      Yes
								    </label> 
								    <label class="radio-inline" for="solvedRadio-1">
								      <input type="radio" {{old('solvedRadio') == "N" ? 'checked="checked"' : ''}} name="solvedRadio" id="solvedRadio-1" value="N">
								      No
								    </label>
								  </div>
								</div>
								<!-- Button Drop Down -->
								<div class="form-group">
								  <label class="col-md-4 control-label" for="timeInput">Length of call</label>
								  <div class="col-md-4">
								    <div class="input-group">
								      <input id="timeInput" name="timeInput" class="form-control" placeholder="Time" type="number" value="{{ old('timeInput') }}">
								      <div class="input-group-btn">
								        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								          Minutes
								          <span class="caret"></span>
								        </button>
								        <ul class="dropdown-menu pull-right">
								          <li><a href="#">5</a></li>
								          <li><a href="#">10</a></li>
								          <li><a href="#">15</a></li>
								          <li><a href="#">20</a></li>
								          <li><a href="#">25</a></li>
								          <li><a href="#">30</a></li>
								        </ul>
								      </div>
								    </div>
								  </div>
								</div>
								<!-- Button -->
								<div class="form-group">
								  <label class="col-md-4 control-label" for="submit"></label>
								  <div class="col-md-4">
								    <button id="submit" name="submit" class="btn btn-success">Submit</button>
								  </div>
								</div>

								</fieldset>
								</form>
								<script src="../../../scripts/typeahead.bundle.js"></script>
								<script>								
										var users = new Bloodhound({
										  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
										  queryTokenizer: Bloodhound.tokenizers.whitespace,
										  remote: {
										    url: 'userJSON?search=%QUERY',
										    wildcard: '%QUERY'
										  }
										});
										 
										$('#userSearch').typeahead(null, {
										limit:25,	
										  name: 'best-pictures',
										  display: 'value',
										  source: users,
										});

										$('#userSearch').bind('typeahead:select', function(ev, suggestion) {
										  $('#selectedUserId').html(' User ID: ' + suggestion['ID']);
										  $('#userID').val(suggestion['ID']);
										});

										$(document).ajaxSend(function(event, jqXHR, settings) {
										    //Call method to display spinner
										    $('#loader').show();
										});

										$(document).ajaxComplete(function(event, jqXHR, settings) {
										    //Call method to hide spinner
										    $('#loader').hide();
										});
										$('.dropdown-menu a').click(function(){
											var dropDownSelection = $(this).html()
											$('#timeInput').val(dropDownSelection);
										});
								</script>
							@else
								
								<form class="form-horizontal" method="POST" action="{{ url('/claimBounty') }}">
								<fieldset>
									<input type="hidden" name="_token" value="{{ csrf_token() }}">
							  		<input id="key" name="key" type="hidden" value="{{$ticket->Key}}">
							  		<input id="userID" type="hidden" name="userID" value="{{ (null !== old('userID')) ? old('userID') : $ticket->UserID }}">
								<!-- Form Name -->
								<legend>Bounty already claimed for <strong>{{ $ticket->CodeName }}</strong></legend>

								<!-- Search input-->
								<div class="form-group">
								  <label class="col-md-4 control-label" for="userSearch" ><img id="loader" class="loader" src="images/ui-anim_basic_16x16.gif" />&nbsp;&nbsp;User</label>
								  <div class="col-md-8">
								    <input id="userSearch" class="userSearch" name="userSearch" type="search" placeholder="User Name" class="typeahead tt-query form-control input-md" autocomplete="off" 
								    value="{{ (null !== old('userSearch')) ? old('userSearch') : $ticket->formattedUserName }}" >
								    <p class="help-block">User that was assisted. <span id="selectedUserId">{{ (null !== old('userID')) ? old('userID') : $ticket->UserID }}</span></p>
								  </div>
								</div>

								<!-- Select Basic -->
								<div class="form-group">
								  <label class="col-md-4 control-label" for="resolutionSelect">Issue Resolution</label>
								  <div class="col-md-6">
								    <select id="resolutionSelect" name="resolutionSelect" class="form-control">
										<option selected disabled hidden value=''>Action Taken</option>
										@foreach($supportTypes as $supportType)
											<option {{ $ticket->SupportTypeID == $supportType->ID ? 'selected' : ''}} value="{{$supportType->ID}}">{{ $supportType->Name }}</option>
										@endforeach
								    </select>
								  </div>
								</div>

								<!-- Textarea -->
								<div class="form-group">
								  <label class="col-md-4 control-label" for="resolutionNotes">Resolution Notes</label>
								  <div class="col-md-4">                     
								    <textarea class="form-control" id="resolutionNotes" name="resolutionNotes" placeholder="Notes...">{{ (null !== old('resolutionNotes')) ? old('resolutionNotes') : $ticket->ResolutionNotes }}</textarea>
								  </div>
								</div>

								<!-- Multiple Radios (inline) -->
								<div class="form-group">
								  <label class="col-md-4 control-label" for="solvedRadio">Resolved?</label>
								  <div class="col-md-4"> 
								    <label class="radio-inline" for="solvedRadio-0">
								      <input type="radio" {{ $ticket->Solved == "Y" ? 'checked="checked"' : ''}} name="solvedRadio" id="solvedRadio-0" value="Y">
								      Yes
								    </label> 
								    <label class="radio-inline" for="solvedRadio-1">
								      <input type="radio" {{ $ticket->Solved == "N" ? 'checked="checked"' : ''}} name="solvedRadio" id="solvedRadio-1" value="N">
								      No
								    </label>
								  </div>
								</div>
								<!-- Button Drop Down -->
								<div class="form-group">
								  <label class="col-md-4 control-label" for="timeInput">Length of call</label>
								  <div class="col-md-4">
								    <div class="input-group">
								      <input id="timeInput" name="timeInput" class="form-control" placeholder="Time" type="number" value="{{ (null !== old('timeInput')) ? old('timeInput') : $ticket->LengthOfCall }}">
								      <div class="input-group-btn">
								        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
								          Minutes
								          <span class="caret"></span>
								        </button>
								        <ul class="dropdown-menu pull-right">
								          <li><a href="#">5</a></li>
								          <li><a href="#">10</a></li>
								          <li><a href="#">15</a></li>
								          <li><a href="#">20</a></li>
								          <li><a href="#">25</a></li>
								          <li><a href="#">30</a></li>
								        </ul>
								      </div>
								    </div>
								  </div>
								</div>
								<!-- Button -->
								<div class="form-group">
								  <label class="col-md-4 control-label" for="submit"></label>
								  <div class="col-md-4">
								    <button id="submit" name="submit" class="btn btn-success">Update</button>
								  </div>
								</div>

								</fieldset>
								</form>
								<script src="../../../scripts/typeahead.bundle.js"></script>
								<script>								
										var users = new Bloodhound({
										  datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
										  queryTokenizer: Bloodhound.tokenizers.whitespace,
										  remote: {
										    url: 'userJSON?search=%QUERY',
										    wildcard: '%QUERY'
										  }
										});
										 
										$('#userSearch').typeahead(null, {
										limit:25,	
										  name: 'best-pictures',
										  display: 'value',
										  source: users,
										});

										$('#userSearch').bind('typeahead:select', function(ev, suggestion) {
										  $('#selectedUserId').html(' User ID: ' + suggestion['ID']);
										  $('#userID').val(suggestion['ID']);
										});

										$(document).ajaxSend(function(event, jqXHR, settings) {
										    //Call method to display spinner
										    $('#loader').show();
										});

										$(document).ajaxComplete(function(event, jqXHR, settings) {
										    //Call method to hide spinner
										    $('#loader').hide();
										});
										$('.dropdown-menu a').click(function(){
											var dropDownSelection = $(this).html()
											$('#timeInput').val(dropDownSelection);
										});
								</script>
							@endif
						</fieldset>
					</div>

					
				</div>
			</div>
		</div>
	</div>
</div>
@endsection