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
  <div style="margin:14pt 0 18.75pt 0; font-size:14px">{!! $replyEmail !!}
  <br/>
  <p>Sincerely,<br/><br/><span style="font-size:16px;font-weight:bold;color:maroon;">{{ $replyAdminName }}</span><br/>Technical Support</p>
  </div>
  <br/>
  <hr/>
  <br/>
  <div style="margin:14pt 0 18.75pt 0; font-size:16px">{!! $ticketEmail !!}</div>
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