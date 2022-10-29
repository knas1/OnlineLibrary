<?php
$title="Messages";
require_once 'template/header.php';
require_once 'config/database.php';
require_once 'config/app.php';

//$messages = $mysqli->query("select *,m.id as message_id, m.name as contacter_name, s.name as service_name from messages m left join services s on m.service_id=s.id")->fetch_all(MYSQLI_ASSOC);

$messageQuery= $mysqli->prepare("select *,m.id as message_id, m.name as contacter_name, s.name as service_name
from messages m left join services s
on m.service_id=s.id
order by m.id limit ?");

isset($_GET['limit']) ? $limit=$_GET['limit'] : $limit=100;

$messageQuery->bind_param('i',$limit);

$messageQuery->execute();

$messages=$messageQuery->get_result()->fetch_all(MYSQLI_ASSOC);

if (!isset($_GET['id'])) {
?>
<h2>Recived Messages</h2>
<div class="table-responsive">
  <table class="table table-hover table-striped">
    <thead>
      <tr>
        <th>#</th>
        <th>Sender Name</th>
        <th>Sender Email</th>
        <th>file</th>
        <th>message</th>
        <th>Service Name</th>
      </tr>
    </thead>
    <tbody>

<?php

foreach ($messages as $message) {
  ?>
  <tr>
    <td><?php echo $message['message_id']; ?></td>
    <td><?php echo $message['contacter_name']; ?></td>
    <td><?php echo $message['email']; ?></td>
    <td><?php echo $message['document']; ?></td>
    <td><?php echo $message['message']; ?></td>
    <td><?php echo $message['service_name']; ?></td>
    <td>
      <a href="?id=<?php echo $message['message_id']; ?>" class="btn btn-sm btn-primary">View</a>
      <form  onsubmit="return confirm('Are you sure?')" action="" method="post" style="display: inline-block">
        <input type="hidden" name="message_id" value="<?php echo $message['message_id'] ?>">
        <button class="btn btn-sm btn-danger">Delete</button>
      </form>
    </td>
  </tr>
<?php
  }
?>
  </tbody>
  </table>
  </div>
<?php

}else {
  $messages = $mysqli->query("select *,m.name as contacter_name, m.id as message_id,s.name as service_name from messages m left join services s on m.service_id=s.id where m.id=".$_GET['id'] . " limit 1")->fetch_array(MYSQLI_ASSOC);

?>
<div class="card">
  <h5 class="card-header">Message from: <?php echo $messages['contacter_name'] ?>
  <div class="small"><?php echo $messages['email'] ?></div>
  </h5>
  <div class="card-body">
    <div>Services: <?php if($messages['service_name']) echo $messages['service_name']; else echo 'Null' ; ?></div>
    <?php echo $messages['message'] ?>
  </div>
  <?php if($messages['document']){ ?>
  <div class="card-footer">
    Attachment: <a href="<?php echo  $config['app_url'].$messages['document']; ?>">Download Attachment</a>
  </div>
<?php } ?>
</div>
<?php
}

if (isset($_POST['message_id'])) {
  //$mysqli->query(" delete from messages where id=".$_POST['message_id']);

  $deleteMessage= $mysqli->prepare("delete from messages where id=? ");
  $deleteMessage -> bind_param('i',$messageId);
  $messageId=$_POST['message_id'];
  $deleteMessage->execute();

  echo "<script>location.href = 'messages.php'</script>";
  die();
}


 require_once 'template/footer.php';
?>
