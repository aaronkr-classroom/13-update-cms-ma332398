<?php
// Part A:설정
declare(strict_types = 1);                                // Use strict types
include '../includes/database-connection.php';            // Database connection
include '../includes/functions.php';                      // Include functions
include '../includes/validate.php'; //검증 파일

// 변수 초기화
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT); // 숫자?
$category = [
  'id'          => $id,
  'name'        => '',
  'description' => '',
  'navigation'  => false,

];
$errors = [
  'warning'     =>'',
  'name'        =>'',
  'description' =>'',
 
];

// 아이디가 있다면, 카데고리를 편집해야 하므로 현재 카테고리 가져옴
if($id) {
  $sql = "SELECT id, name, description, navigation
          FROM category WHERE id = :id;";
  $category = pdo($pdo, $sql, [$id])->fetch();
  if (!$category) {
    redirect(
      'categories.php',
      ['failure' => 'Category not found.']
    );
  }
}
// Slide 26 코드 
// Part B: 데이터 가져와서 유효성 검사
if ($_SERVER['REQUEST_METHOD'] == 'POST') { //폼 제출!
  $category['neme'] = $_POST['neme'];
  $category['description'] = $_POST['description'];
  $category['navigation'] = (isset($_POST['navigation'])
      and ($_POST['navigation'] == 1)) ? 1 : 0;

// 모든 데이터가 유효한지 확인하고 유효하지 않다면 오류 메시지 생성
$errors['name'] = (is_text($category['neme'], 1, 24))
    ? '' : 'Name should be 1-24 characters';
$errorsp['description'] = (is_text($category['description'], 1, 254))
    ? '' : 'Description should be 1-254 characters';
$invalid = implode($errors); // 오류 메시지 결합하기 

// Slide 27 코드
// Part C : 데이터가 유효하지 확인, 유효하다면 데이터베이스 업데이트 
if ($invalid) {
  $errors['warning'] = 'Please fix errors.';
} else {
  $arguments = $category;
  if ($id) { // UPDATE(이미 있는 데이터 수정)
    $sql = "UPDATE category
            SET name = :name, descripon = :description,
                navigation = :navigation
            WHERE id = :id;";
  } else { // INSERT (새로운 데이터 추가 / 생성)
    unset($arguments['id']); // 카데고리 배열에서 아이디어 제거
    $sql = "INSERT INTO category (name, description, navigation)
            VALUES (:name, :description, :navigation);";
  } 
}
// SQL을 실행할 때, 세 가지가 발생할 수 있음 
  try { // 1. 카데고리 저장
    pdo($pdo, $sql, $arguments); // 성공하면 저장
    redirect('categories.php', ['success' => 'Category saved!']);
  } catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) { // 2. 이름이 이미 사용됨
      $error['warnig'] = 'Category name already in use!';
    } else { // 3. 다른 이유로 예외 발생
      throw $e;
    }
  }
}
?>
<?php include '../includes/admin-header.php'; ?>
  <main class="container admin" id="content">
    <form action="category.php?id=<?= $id ?>" method="post" class="narrow">
      <h1>Edit Category</h1>
      <?php if ($errors['warning']) { ?>
        <div class="alert alert-danger"><?= $errors['warning'] ?></div>
      <?php } ?>

      <div class="form-group">
        <label for="name">Name: </label>
        <input type="text" name="name" id="name"
               value="<?= html_escape($category['name']) ?>" class="form-control">
        <span class="errors"><?= $errors['name'] ?></span>
      </div>

      <div class="form-group">
        <label for="description">Description: </label>
        <textarea name="description" id="description"
                  class="form-control"><?= html_escape($category['description']) ?></textarea>
        <span class="errors"><?= $errors['description'] ?></span>
      </div>

      <div class="form-check">
        <input type="checkbox" name="navigation" id="navigation"
               value="1" class="form-check-input"
          <?= ($category['navigation'] === 1) ? 'checked' : '' ?>>
        <label class="form-check-label" for="navigation">Navigation</label>
      </div>

      <input type="submit" value="Save" class="btn btn-primary btn-save">
    </form>
  </main>
<?php include '../includes/admin-footer.php'; ?>