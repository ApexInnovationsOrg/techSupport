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
  <p style="font-size:20px">Dearest tech support members,</p>
  <div style="margin:14pt 0 18.75pt 0; font-size:16px">{{ $employeeName }} has started the ticket <b>{{ $codeName }}</b> at {{ $started }}</div>
  <ol>
    <li>Call (337)216-4599</li>
    <li>**111 for voicemail</li>
    <li>9999# for the password</li>
    <li>dial 1 to listen to the message</li>
    <li>hit * for the main prompt to get to call bridge</li>
    <li>Soon as you hear welcome, hit 9</li>
    <li>"This is callbridge", hit 9988#</li>
    <li>1 + the number</li>
  </ol>
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

