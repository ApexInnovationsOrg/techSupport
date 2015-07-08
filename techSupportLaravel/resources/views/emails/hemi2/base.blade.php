@extends('emails.hemi2.default')

@section('content')
<td colspan="2"  style="margin-top:15px; padding:40.5pt 0pt 0pt 0pt;">
  <table border="0" cellspacing="0" cellpadding="0" width="100%">
    <tbody>
        <tr>
            <td align="center">
                <img src="<?php echo $message->embed('images/hemi2/' . isset($image) ? $image : 'Non_Hemi_Admins' . '.png'); ?>" style="padding-bottom: 10px;" alt="Hemispheres 2.0" />
            </td>
        </tr>
    </tbody>
</table>
</td>
@endsection

