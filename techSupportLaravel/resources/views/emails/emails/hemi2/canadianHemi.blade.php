@extends('emails.hemi2.defaultHemi')

@section('content')
<td colspan="2"  style="margin-top:15px; padding:0pt 40.5pt 0 40.5pt;">
<style>
a:visited{
    color:#ffffff;
}
a{
    text-decoration:none;
}
</style>
<p style="font-size:20px">{{ $name }},</p>

  <div style="margin:14pt 0 18.75pt 0; font-size:16px">
    <p>
    You may have heard about the recent upgrade to Hemispheres 2.0.  <b>We're here to let you know the Apex team has already started working on the upgrade for Canadian Hemispheres 2.0 and we are hoping to have it out to you in a few months!</b></p>
    <p>Stay tuned as we prepare to make your stroke educational experience even better!</p>
  </div>

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
