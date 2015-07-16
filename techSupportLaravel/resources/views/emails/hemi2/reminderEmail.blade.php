@extends('emails.hemi2.default')

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
    If you're receiving this email, you are eligible to upgrade your organization to Hemispheres 2.0! (It's <u>free</u>!)</p>
    <p>Press the upgrade button on the My Curriculum page, and that's it!</p>
    <a href="https://login.apexinnovations.com"><img src="<?php echo $message->embed('https://s3.amazonaws.com/uploads.hipchat.com/133998/972960/nCFjZLsnYfYugsG/upload.png'); ?>" style="" alt="Apex Innovations" /></a>
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
