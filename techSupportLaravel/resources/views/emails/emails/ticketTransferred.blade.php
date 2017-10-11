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

  <div style="margin:14pt 0 18.75pt 0; font-size:16px">Ticket transferred to you.</div>
  <ol>
    <li>Call (337)216-4599</li>
    <li>**111 for voicemail</li>
    <li>9999# for the password</li>
    <li>dial 1 to listen to the message</li>
    <li>hit * for the main prompt to get to call bridge</li>
    <li>Soon as you hear welcome, hit 9</li>
    <li>"This is callbridge", hit 30942353#</li>
    <li>1 + the number</li>
  </ol>
  <table border="1" cellspacing="0" cellpadding="0" style="background-color:#337ab7;border-style:none none solid none;border-bottom-width:1.5pt;border-bottom-color:#2e6da4; text-align:center;">
    <tbody>
          <tr>
            <td style="padding:0;border-style:none;">
                <div style="margin:0;">
                </div>
            </td>
            <td style="padding:0 17.25pt 1.5pt 18pt;border-style:none;">
                <div align="center" style="text-align:center;margin:10px;">
                    <font face="Times New Roman,serif" size="3"><span style="font-size:12pt;"><a href={{ url('admin/techSupport/showTicket/?key='.$key) }} target="_blank" style="text-decoration:none; color:#ffffff"><font face="Segoe UI,sans-serif" size="4"><span style="font-size:13.5pt;"><font size="4" color="white"><span style="font-size:15pt;" style="color:#ffffff !important">Finish the ticket!&nbsp;&nbsp;></span></font>
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

