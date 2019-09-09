@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<div class="panel-heading clearfix">Tickets</br>Bounty Claimed: <span style="color:green">${{ $bounty['TotalBounty'] }}</span></br>Tickets Completed: {{ $bounty['BountiesClaimed'] }}</div>
				<script type="text/javascript" src="../../../scripts/jquery.dynatable.js"></script>
				<div>
					@if(Session::has('message'))
					    <div class="alert alert-success">
					        <h2>{{ Session::get('message') }}</h2>
					    </div>
					@endif


					@if ($errors && count($errors) > 0)
						<div class="alert alert-danger">
							<strong>Whoops!</strong> There were some problems with your input.<br><br>
							<ul>
								@foreach ($errors->all() as $error)
									<li>{{ $error }}</li>
								@endforeach
							</ul>
						</div>
					@endif
					<h4>Your Tickets</h4>
					@if ($tickets && count($tickets) > 0)
						<table id="claimedTable" class="bordered-table zebra-striped dataTable no-footer">
						  <thead>
						    <th>Code Name</th>
						    <th>Claim Date</th>
						    <th>Completed</th>
						    <th>Bounty Claimed</th>
						    <th>Ticket</th>
						  </thead>
						  <tbody>
						  </tbody>
						</table>
						
							<script>
								var claimedTickets = {!! $tickets !!}
								$('#claimedTable').dynatable({
								  dataset: {
									records: claimedTickets
								  },
								  table : { copyHeaderClass: true }
								 });
							</script>
					@elseif ($tickets && count($tickets) == 0)
							
						<div class="alert alert-success" role="alert">
					      You Have 0 unfinished tickets
					    </div>
					
					@endif
				</div>
				<hr/>
				<div>	
					<h4>Unclaimed tickets</h4>
						<table id="unclaimedTable" class="bordered-table zebra-striped dataTable no-footer">
						  <thead>
						    <th>Code Name</th>
						    <th>Creation Date</th>
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
							  }
							 });
						</script>
				</div>
		</div>
	</div>
</div>

@endsection
