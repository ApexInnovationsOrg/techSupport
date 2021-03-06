@extends('app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1">
			<div class="panel panel-default">
				<script type="text/javascript" src="../../../scripts/jquery.dynatable.js"></script>
				<h3>Emails</h3>
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
					<div>	
						<ol>
							<li><a href={{ url('email/test') }}>Test</a></li>
							<li><a href={{ url('email/sepsis') }}>All Users</a></li>
<!-- 							<li><a href={{ url('email/imp2admin') }}>Impulse 2.0 test questions admins</a></li>
							<li><a href={{ url('email/imp2user') }}>Impulse 2.0 test questions users</a></li> -->
							<!-- <li><a href={{ url('email/CAhemiAdmins') }}>hemiAdmins</a></li>
							<li><a href={{ url('email/hemiStore') }}>hemiStore</a></li>
							<li><a href={{ url('email/expCAHemiAdmins') }}>expHemiAdmins</a></li>
							<li><a href={{ url('email/expHemiStore') }}>expHemiStore</a></li>
							<li><a href={{ url('email/nonHemiAdmins') }}>nonHemiAdmins</a></li>
							<li><a href={{ url('email/nonHemiStore') }}>nonHemiStore</a></li>
							<li><a href={{ url('email/canadianHemi') }}>canadianHemi</a></li> -->
						</ol>
					</div>
		</div>
	</div>
</div>

@endsection
