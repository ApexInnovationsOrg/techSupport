@extends('emails.default')

@section('content')
<td colspan="2"  style="margin-top:15px; padding:40.5pt 40.5pt 0 40.5pt;">
<style>
a:visited{
    color:#ffffff;
}
a{
    text-decoration:none;
}
</style>
  <div style="margin:14pt 0 18.75pt 0; font-size:16px">{{ $userName }},<br/><br/>Thank you for starting a support ticket. We'll get with you ASAP!<br/>Below is a receipt of your submitted tech support ticket:</div>
  <hr/>
  <div style="margin:14pt 0 18.75pt 0; font-size:16px">Contact: {{ $contact }}</div>
  <div style="margin:14pt 0 18.75pt 0; font-size:16px">Overview: {{ $overview }}</div>
  <div style="margin:14pt 0 18.75pt 0; font-size:16px">Description: {{ $description }}</div>
<table border="1" cellspacing="0" cellpadding="0" style="background-color:#337ab7;border-style:none none solid none;border-bottom-width:1.5pt;border-bottom-color:#2e6da4; text-align:center;">
    <tbody>
        <tr>
            <td style="padding:0;border-style:none;">
                <div style="margin:0;">
                </div>
            </td>
        </tr>
    </tbody>
</table>
<br/>
<br/>
</td>
@endsection