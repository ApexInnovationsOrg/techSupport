@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading clearfix">Ticket Stats</div>
				<script type="text/javascript" src="../../../scripts/jquery.dynatable.js"></script>
				@if($admin)
					<div>
						<h4>Claimed Bounties</h4>

							<table id="claimedTable" class="bordered-table zebra-striped dataTable no-footer">
							  <thead>
							    <th>Code Name</th>
							    <th>Completed</th>
							    <th data-dynatable-sorts="bountyClaimedInteger">Bounty Claimed</th>
							    <th style="display:none">Bounty Claimed Integer</th>
							    <th>Employee</th>
							    <th>Show Ticket</th>
							  </thead>
							  <tbody>
							  </tbody>
							</table>
							

							<script>
								var bounty = {!! $bounty !!}
								$('#claimedTable').dynatable({
								  dataset: {
									records: bounty
								  },
								  table : { copyHeaderClass: true }
								 });
							</script>
					</div>
					<div>
						<h4>Unclaimed Tickets</h4>

							<table id="unclaimedTable" class="bordered-table zebra-striped dataTable no-footer">
							  <thead>
							    <th>Code Name</th>
							    <th>Created</th>
							    <th>Claim Ticket</th>
							  </thead>
							  <tbody>
							  </tbody>
							</table>
							

							<script>
								var unclaimedTickets = {!! $unclaimedTickets !!}
								$('#unclaimedTable').dynatable({
								  dataset: {
									records: unclaimedTickets
								  },
								  table : { copyHeaderClass: true }
								 });
							</script>
					</div>
					<div>
						<h4>All Tickets</h4>

							<table id="allTicketsTable" class="bordered-table zebra-striped dataTable no-footer">
							  <thead>
							    <th>Code Name</th>
							    <th>Completed</th>
							    <th>User</th>
							    <th>Employee</th>
							    <th>Show Ticket</th>
							    <th>Archive Ticket</th>
							  </thead>
							  <tbody>
							  </tbody>
							</table>
							<script>
								var tickets = {!! $tickets !!}
								$('#allTicketsTable').dynatable({
								  dataset: {
									records: tickets
								  },
								  table : { copyHeaderClass: true }
								 });

								$(document).ready(function(){
									$('.archiveTicket').click(function(){
											var $archiveTicket = $(this);
											$.ajax({
												method: "POST",
											  url: "archiveTicket",
											  data: {	
											  "_token":"{{ csrf_token() }}",
											  	'key': $archiveTicket.data('key')
											  }	
											}).done(function(data) {
												var data = JSON.parse(data);
											 	if(data.success == true)
											 	{
											 	console.log('hello?');
											 	 $archiveTicket.parents('tr').fadeOut();
											 	}
											 	else
											 	{
											 		alert('Error archiving item');
											 	}
											});
									});
								});
							</script>
					</div>
				@else
					<div>
						<h4>Claimed Bounties</h4>

							<table id="claimedTable" class="bordered-table zebra-striped dataTable no-footer">
							  <thead>
							    <th>Code Name</th>
							    <th>Completed</th>
							    <th>Bounty Claimed</th>
							    <th>User</th>
							    <th>Show Ticket</th>
							  </thead>
							  <tbody>
							  </tbody>
							</table>
							

							<script>
								var bounty = {!! $bounty !!}
								$('#claimedTable').dynatable({
								  dataset: {
									records: bounty
								  },
								  table : { copyHeaderClass: true }
								 });
							</script>
					</div>
					<div>
						<h4>Unclaimed Tickets</h4>

							<table id="unclaimedTable" class="bordered-table zebra-striped dataTable no-footer">
							  <thead>
							    <th>Code Name</th>
							    <th>Created</th>
							    <th>Claim Ticket</th>
							  </thead>
							  <tbody>
							  </tbody>
							</table>
							

							<script>
								var unclaimedTickets = {!! $unclaimedTickets !!}
								$('#unclaimedTable').dynatable({
								  dataset: {
									records: unclaimedTickets
								  },
								  table : { copyHeaderClass: true }
								 });
							</script>
					</div>
					<div>
						<h4>All Tickets</h4>

							<table id="allTicketsTable" class="bordered-table zebra-striped dataTable no-footer">
							  <thead>
							    <th>Code Name</th>
							    <th>Completed</th>
							    <th>User</th>
							    <th>Show Ticket</th>
							  </thead>
							  <tbody>
							  </tbody>
							</table>
							<script>
								var tickets = {!! $tickets !!}
								$('#allTicketsTable').dynatable({
								  dataset: {
									records: tickets
								  },
								  table : { copyHeaderClass: true }
								 });
							</script>
					</div>
				@endif
		</div>
	</div>
</div>

@endsection
