@extends('emails.impulse2TQ.default')

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

<p style="margin-top:14pt; font-size:16px;">{{ $name }},</p>

  <div style="margin:14pt 0 18.75pt 0; font-size:16px">
    <p>Recently, our clinical team completed a comprehensive review of each test question in the imPULSE 2.0 series with a goal of ensuring EVERY test question was clear, simple, clinically meaningful, and relevant to practice.</p>

    <p>We have:
        <ol>
            <li>Adjusted or inactivated questions where needed.</li>
            <li>Removed redundant questions from our test item pool.</li>
            <li>Improved lead placement exercises by automatically zooming and expanding zones for R/L arm and precordial/chest electrode placement.</li>
        </ol>
    </p>
    <p>We strive to deliver the very best continuing education programs possible and will continue to do this by listening to your feedback. We want to meet and exceed your expectations regarding education and competency. If you have any suggestions or thoughts, please contact us.
    </p>

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
