<?php
require 'Config.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : null;
    $nama = trim($_POST['nama'] ?? '');
    $ttl = !empty($_POST['tempat_tanggal_lahir']) ? $_POST['tempat_tanggal_lahir'] : null; 
    $no_telp = trim($_POST['no_telp'] ?? '');
    $jenis_kelamin = $_POST['jenis_kelamin'] ?? null;
    $agama = $_POST['agama'] ?? null;
    $hobi = '';
    if (!empty($_POST['hobi']) && is_array($_POST['hobi'])) {
        $hobi = implode(',', array_map('trim', $_POST['hobi']));
    }

    if ($nama === '') {
        $msg = "Nama wajib diisi.";
    } else {
        if ($id) {
            $sql = "UPDATE biodata SET nama = :nama, tempat_tanggal_lahir = :ttl, alamat = :alamat, no_telp = :no_telp, jenis_kelamin = :jk, agama = :agama, hobi = :hobi WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nama' => $nama,
                ':ttl' => $ttl,
                ':alamat' => $alamat,
                ':no_telp' => $no_telp,
                ':jk' => $jenis_kelamin,
                ':agama' => $agama,
                ':hobi' => $hobi,
                ':id' => $id,
            ]);
            $msg = "Data updated (ID: $id).";
        } else {
            $sql = "INSERT INTO biodata (nama, tempat_tanggal_lahir, alamat, no_telp, jenis_kelamin, agama, hobi) VALUES (:nama, :ttl, :alamat, :no_telp, :jk, :agama, :hobi)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nama' => $nama,
                ':ttl' => $ttl,
                ':alamat' => $alamat,
                ':no_telp' => $no_telp,
                ':jk' => $jenis_kelamin,
                ':agama' => $agama,
                ':hobi' => $hobi,
            ]);
            $msg = "Data inserted. New ID: " . $pdo->lastInsertId();
        }
    }
}

$record = null;
if (!empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM biodata WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $record = $stmt->fetch();
}

$searchResults = [];
if (!empty($_GET['search'])) {
    $q = trim($_GET['search']);
    $stmt = $pdo->prepare("SELECT id, nama, tempat_tanggal_lahir FROM biodata WHERE nama LIKE :q ORDER BY id DESC LIMIT 50");
    $stmt->execute([':q' => "%$q%"]);
    $searchResults = $stmt->fetchAll();
}

$latest = $pdo->query("SELECT id, nama FROM biodata_db ORDER BY id DESC LIMIT 10")->fetchAll();

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Form Input Biodata</title>
<style>
.container { width: 760px; margin: 30px auto; border: 1px solid #333; padding: 18px; font-family: Arial; }
h1 { text-align:center; margin:0 0 12px; }
.form-row { margin: 8px 0; }
label { display:inline-block; width:160px; vertical-align:top; }
input[type="text"], input[type="date"], select, textarea { width: 320px; padding:4px; }
textarea { height:60px; }
.small { width:140px; }
.notice { color: green; margin:8px 0; }
.error { color: red; margin:8px 0; }
.list { margin-top:12px; }
a.id-link { margin-right: 8px; text-decoration:none; }
</style>
</head>
<body>
<div class="container">
<h1>Form Input Biodata</h1>

<?php if ($msg): ?>
  <div class="notice"><?=htmlspecialchars($msg)?></div>
<?php endif; ?>

<form method="post" action="">
  <input type="hidden" name="id" value="<?= $record ? htmlspecialchars($record['id']) : '' ?>">

  <div class="form-row">
    <label>Nama</label>
    <input type="text" name="nama" value="<?= $record ? htmlspecialchars($record['nama']) : '' ?>">
  </div>

  <div class="form-row">
    <label>Tempat, Tanggal Lahir</label>
    <input type="date" name="tempat_tanggal_lahir" value="<?= $record && $record['tempat_tanggal_lahir'] ? htmlspecialchars($record['tempat_tanggal_lahir']) : '' ?>">
  </div>

  <div class="form-row">
    <label>Alamat</label>
    <textarea name="alamat"><?= $record ? htmlspecialchars($record['alamat']) : '' ?></textarea>
  </div>

  <div class="form-row">
    <label>No.Telp/HP</label>
    <input type="text" name="no_telp" value="<?= $record ? htmlspecialchars($record['no_telp']) : '' ?>">
  </div>

  <div class="form-row">
    <label>Jenis Kelamin</label>
    <label><input type="radio" name="jenis_kelamin" value="Laki-Laki" <?= ($record && $record['jenis_kelamin']=='Laki-Laki')?'checked':'' ?>> Laki-Laki</label>
    <label><input type="radio" name="jenis_kelamin" value="Perempuan" <?= ($record && $record['jenis_kelamin']=='Perempuan')?'checked':'' ?>> Perempuan</label>
  </div>

  <div class="form-row">
    <label>Agama</label>
    <select name="agama">
      <option value="">-- pilih --</option>
      <?php
      $ops = ['ISLAM','KRISTEN','KATOLIK','HINDU','BUDDHA','LAINNYA'];
      foreach ($ops as $o) {
          $sel = ($record && $record['agama']==$o) ? 'selected' : '';
          echo "<option value=\"".htmlspecialchars($o)."\" $sel>".htmlspecialchars($o)."</option>";
      }
      ?>
    </select>
  </div>

  <div class="form-row">
    <label>Hobi</label>
    <?php
      $hArr = $record && $record['hobi'] ? explode(',', $record['hobi']) : [];
      $hList = ['Baca Buku','OlahRaga','Main Game','Koding'];
      foreach ($hList as $h) {
          $checked = in_array($h, $hArr) ? 'checked' : '';
          echo "<label><input type=\"checkbox\" name=\"hobi[]\" value=\"".htmlspecialchars($h)."\" $checked> $h</label> ";
      }
    ?>
  </div>

  <div class="form-row">
    <label></label>
    <button type="submit">Submit</button>
    <button type="reset">Reset</button>
  </div>
</form>

<hr>

<div>
  <form method="get" action="">
    <label style="width:auto">Cari Nama:</label>
    <input type="text" name="search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
    <button type="submit">Search</button>
  </form>
</div>

<?php if (!empty($searchResults)): ?>
  <h3>Hasil pencarian:</h3>
  <ul>
    <?php foreach ($searchResults as $r): ?>
      <li>
        <a class="id-link" href="?id=<?= $r['id'] ?>"><?= $r['id'] ?></a>
        <?= htmlspecialchars($r['nama']) ?> (<?= htmlspecialchars($r['tempat_tanggal_lahir']) ?>)
      </li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<div class="list">
  <h4>Latest entries (click id to load into form)</h4>
  <?php if ($latest): ?>
    <?php foreach ($latest as $r): ?>
      <a class="id-link" href="?id=<?= $r['id'] ?>">[<?= $r['id'] ?>]</a> <?= htmlspecialchars($r['nama']) ?><br>
    <?php endforeach; ?>
  <?php else: ?>
    Tidak ada data.
  <?php endif; ?>
</div>

</div>
</body>
</html>
