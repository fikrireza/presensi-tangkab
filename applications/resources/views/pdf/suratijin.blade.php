<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title></title>
  </head>
  <body>
    <table border="0" cellspacing="0" style="width:100%;" cellpadding="10">
      <tr>
        <td width="400px;">
          Hal: <strong><u>{{$data["nama_intervensi"]}}</u></strong>
        </td>
        <td>
          Tigaraksa, {{$data["tanggal"]}}.
        </td>
      </tr>
      <tr>
        <td>

        </td>
        <td>
          Kepada:
        </td>
      </tr>
      <tr>
        <td>

        </td>
        <td width="200px;">
          Yth. {{$data["jabatan_atasan"]}}
        </td>
      </tr>
      <tr>
        <td>

        </td>
        <td>
          di- TEMPAT
        </td>
      </tr>
    </table>
    <br>
    <br>
    <br>
    <table border="0" cellspacing="0" style="width:100%;" cellpadding="10">
      <tr>
        <td colspan="3">
          Yang bertanda tangan di bawah ini:
        </td>
      </tr>
      <tr>
        <td>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nama
        </td>
        <td style="width:10px;">
          :
        </td>
        <td>
          {{$data["nama_pegawai"]}}
        </td>
      </tr>
      <tr>
        <td>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;NIP
        </td>
        <td style="width:10px;">
          :
        </td>
        <td>
          {{$data["nip_pegawai"]}}
        </td>
      </tr>
      <tr>
        <td style="width:300px;">
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Pangkat/Gol/Ruang
        </td>
        <td style="width:10px;">
          :
        </td>
        <td>
          {{$data["jabatan_pegawai"]}}
        </td>
      </tr>
      <tr>
        <td colspan="3">
          Dengan ini menerangkan {{$data["nama_intervensi"]}} pada:
        </td>
      </tr>
      @if ($data["jam_ijin"]!="")
        <tr>
          <td style="width:100px;">
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Jam
          </td>
          <td style="width:10px;">
            :
          </td>
          <td>
            {{$data["jam_ijin"]}}
          </td>
        </tr>
      @endif
      <tr>
        <td style="width:100px;">
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Tanggal
        </td>
        <td style="width:10px;">
          :
        </td>
        <td>
          {{$data["tanggal_ijin"]}}
        </td>
      </tr>
      <tr>
        <td colspan="3">
          Dikarenakan {{$data["keterangan"]}}.
        </td>
      </tr>
      <tr>
        <td colspan="3">
          Demikian keterangan ini saya sampaikan. Atas perhatiannya, saya ucapkan terima kasih.
        </td>
      </tr>
    </table>
    <br>
    <br>
    <br>
    <table border="0" cellspacing="0" style="width:100%;" cellpadding="10">
      <tr>
        <td>
          Mengetahui,
        </td>
      </tr>
      <tr>
        <td width="700px;">
          Atasan Langsung
        </td>
        <td>
          Yang Menerangkan
        </td>
      </tr>
      <tr>
        <td>
          {{$data["atasan_langsung"]}}
        </td>
        <td>
          {{$data["nama_pegawai"]}}
        </td>
      </tr>
      <tr>
        <td>
          <br>
          <br>
          <br>
          <br>
          <br>
        </td>
        <td>
          <br>
          <br>
          <br>
          <br>
          <br>
        </td>
      </tr>
      <tr>
        <td>
          ---------------------------------
        </td>
        <td>
          ---------------------------------
        </td>
      </tr>
      <tr>
        <td>
          NIP. {{$data["nip_atasan"]}}
        </td>
        <td>
          NIP. {{$data["nip_pegawai"]}}
        </td>
      </tr>
    </table>
  </body>
</html>
