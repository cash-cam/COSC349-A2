
<!DOCTYPE html>
<html>
<head><title>Otago Student Result Checker</title></head>
<body>
  <h1 style="text-decoration: underline;">Otago Student Result Checker</h1>
  <h2>Enter your Email and Student ID below to see results</h2>
  <form action="login.php" method="post" autocomplete="username" 
 >

  <input type="text" name="email" placeholder="Email" required>
  <br>


  <input type="password" placeholder="Student ID" name="student_id" required>
  <br>

  <button type="submit" style="margin-top: 10px;">log in</button>
  
</form >
  
    <h3>Current Papers on Offer </h3>
<?php
    require __DIR__.'/db.php';
    $q = $pdo->query("SELECT code, name FROM papers ORDER BY code");
    echo '<table border="1"><tr><th>Paper code</th><th>Paper name</th></tr>';
    foreach ($q as $row) {
      echo '<tr><td>'.htmlspecialchars($row['code']).'</td><td>'.htmlspecialchars($row['name']).'</td></tr>';
    }
    echo '</table>';
  ?>
  </table>
</body>
</html>



