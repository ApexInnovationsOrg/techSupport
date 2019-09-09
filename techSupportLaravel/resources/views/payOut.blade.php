@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<script type="text/javascript" src="../../../scripts/jquery.dynatable.js"></script>
				<h3>Payouts</h3>
				<hr/>
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
					<div>	
						<table id="payOutTable" class="bordered-table zebra-striped dataTable no-footer">
						  <thead>
						    <th>Person</th>
						    <th>Total Tickets</th>
						    <th>Average Time</th>
						    <th>Total Time</th>
						    <th>Payout</th>
						    <th>Pay</th>
						  </thead>
						  <tbody>
						  </tbody>
						</table>
						
						<script>
							var payOut = {!! $payOut !!}
							$('#payOutTable').dynatable({
							  dataset: {
								records: payOut
							  }
							 });
						</script>
					</div>

		</div>
	</div>
</div>

@endsection
