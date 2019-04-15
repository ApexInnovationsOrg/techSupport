@extends('emails.default')

@section('content')
<td colspan="2"  style="margin-top:15px; padding:40.5pt 40.5pt 0 40.5pt;">
<style>
</style>
  <p style="font-size:16px">{{ $adminName }},</p>
  <div style="margin:14pt 0 18.75pt 0; font-size:16px">Attached is the requested report.<br/><br/>
  <strong><u>Report Details:</u></strong><br/>
  @foreach ($reportParams as $key => $val)
		{{ $key }} : {{ $val }}<br/>
  @endforeach
  </div>
	<br/>
	<br/>
</td>
@endsection

