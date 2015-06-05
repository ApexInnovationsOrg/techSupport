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
  <p style="font-size:20px">Hi there,</p>
  <div style="margin:14pt 0 18.75pt 0; font-size:16px">Thanks for contacting support! Please verify that you are in fact NOT spam by clicking the button below.</div>
  <table border="1" cellspacing="0" cellpadding="0" style="background-color:#33B73F;border-style:none none solid none;border-bottom-width:1.5pt;border-bottom-color:#35A42E; text-align:center;">
    <tbody>
        <tr>
            <td style="padding:0;border-style:none;">
                <div style="margin:0;">
                </div>
            </td>
            <td style="padding:0 17.25pt 1.5pt 18pt;border-style:none;">
                <div align="center" style="text-align:center;margin:10px;">
                    <font face="Times New Roman,serif" size="3"><span style="font-size:12pt;"><a href={{ isset($url) ? $url : '#' }} target="_blank" style="text-decoration:none; color:#ffffff"><font face="Segoe UI,sans-serif" size="4"><span style="font-size:13.5pt;"><font size="4" color="white"><span style="font-size:15pt;" style="color:#ffffff !important">I AM NOT SPAM!&nbsp;&nbsp;></span></font>
                    </span>
                    </font>
                    </a>
                    </span>
                    </font>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<br/>
<br/>
</td>
@endsection

