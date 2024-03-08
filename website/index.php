<?php
require_once 'class/config.php';
require_once 'class/database.php';
require_once 'steamauth/steamauth.php';
require_once 'class/utils.php';

$db = new DataBase();

// 如果用户已经登录
if (isset($_SESSION['steamid'])) {
	// 获取用户的Steam ID
	$steamid = $_SESSION['steamid'];
	$weapons = UtilsClass::getWeaponsFromArray();
	$skins = UtilsClass::skinsFromJson();
	$querySelected = $db->select("SELECT `weapon_defindex`, `weapon_paint_id`, `weapon_wear`, `weapon_seed` FROM `wp_player_skins` WHERE `wp_player_skins`.`steamid` = :steamid", ["steamid" => $steamid]);
	$selectedSkins = UtilsClass::getSelectedSkins($querySelected);
	$selectedKnife = $db->select("SELECT * FROM `wp_player_knife` WHERE `wp_player_knife`.`steamid` = :steamid", ["steamid" => $steamid]);
	$selectedAgent = $db->select("SELECT `agent_ct` ,`agent_t` FROM `wp_player_agents` WHERE `steamid` = :steamid", ["steamid" => $steamid]);
	$selectedGloves = $db->select("SELECT `weapon_defindex` FROM `wp_player_gloves` WHERE `steamid` = :steamid", ["steamid" => $steamid]);
	$knifes = UtilsClass::getKnifeTypes();
	$gloves = UtilsClass::getGloves();
	if (isset($_POST['forma'])) {
		$ex = explode("-", $_POST['forma']);

		// 如果用户选择了刀
		if ($ex[0] == "knife") {
			$db->query("INSERT INTO `wp_player_knife` (`steamid`, `knife`) VALUES(:steamid, :knife) ON DUPLICATE KEY UPDATE `knife` = :knife", ["steamid" => $steamid, "knife" => $knifes[$ex[1]]['weapon_name']]);

		} else {
			// 如果用户选择了皮肤，并且磨损度在0.00到1.00之间，并且种子存在
			if (array_key_exists($ex[1], $skins[$ex[0]]) && isset($_POST['wear']) && $_POST['wear'] >= 0.00 && $_POST['wear'] <= 1.00 && isset($_POST['seed'])) {
				$wear = floatval($_POST['wear']); // 磨损度
				$seed = intval($_POST['seed']); // 种子
				// 如果用户已经选择了这种皮肤，就更新皮肤信息
				if (array_key_exists($ex[0], $selectedSkins)) {
					$db->query("UPDATE wp_player_skins SET weapon_paint_id = :weapon_paint_id, weapon_wear = :weapon_wear, weapon_seed = :weapon_seed WHERE steamid = :steamid AND weapon_defindex = :weapon_defindex", ["steamid" => $steamid, "weapon_defindex" => $ex[0], "weapon_paint_id" => $ex[1], "weapon_wear" => $wear, "weapon_seed" => $seed]);
				} else {
					// 如果用户没有选择这种皮肤，就插入新的皮肤信息
					$db->query("INSERT INTO wp_player_skins (`steamid`, `weapon_defindex`, `weapon_paint_id`, `weapon_wear`, `weapon_seed`) VALUES (:steamid, :weapon_defindex, :weapon_paint_id, :weapon_wear, :weapon_seed)", ["steamid" => $steamid, "weapon_defindex" => $ex[0], "weapon_paint_id" => $ex[1], "weapon_wear" => $wear, "weapon_seed" => $seed]);
				}
			}
		}
		// 提交表单后重定向到当前页面
		header("Location: {$_SERVER['PHP_SELF']}");
	}
}


?>

<!DOCTYPE html>
<html lang="en" <?php if (WEB_STYLE_DARK)
	echo 'data-bs-theme="dark"' ?>>

	<head>
		<meta charset="utf-8">
		<link rel="stylesheet" href="style.css">
		<title>CS2皮肤修改web|yumi</title>
	</head>

	<body>

		<?php
if (!isset($_SESSION['steamid'])) {
	echo "<div class='bg-primary'><h2>选择武器涂装配置，请登录-> ";
	loginbutton("rectangle");
	echo "</h2></div>";
} else {
	echo "<div class='bg-primary'><h2>您当前的武器皮肤装备<a class='btn btn-danger' href='{$_SERVER['PHP_SELF']}?logout'>登出</a></h2> </div>";
	echo "<div class='card-group mt-2'>";
	?>

		<div class="col-sm-2">
			<div class="card text-center mb-3 border border-primary">
				<div class="card-body">
					<?php 
					$actualKnife = $knifes[0];
					if ($selectedKnife != null) {
						foreach ($knifes as $knife) {
							if ($selectedKnife[0]['knife'] == $knife['weapon_name']) {
								$actualKnife = $knife;
								break;
							}
						}
					}

					echo "<div class='card-header'>";
					echo "<h6 class='card-title item-name'>刀类型</h6>";
					echo "<h5 class='card-title item-name'>{$actualKnife["paint_name"]}</h5>";
					echo "</div>";
					echo "<img src='{$actualKnife["image_url"]}' class='skin-image'>";
					?>
				</div>
				<div class="card-footer">
					<form action="" method="POST">
						<select name="forma" class="form-control select" onchange="this.form.submit()" class="SelectWeapon">
							<option disabled>选择刀</option>
							<?php
							foreach ($knifes as $knifeKey => $knife) {
								if ($selectedKnife[0]['knife'] == $knife['weapon_name'])
									echo "<option selected value=\"knife-{$knifeKey}\">{$knife['paint_name']}</option>";
								else
									echo "<option value=\"knife-{$knifeKey}\">{$knife['paint_name']}</option>";
							}
							?>
						</select>
					</form>
				</div>
			</div>
		</div>

		<div class="col-sm-2">
			<div class="card text-center mb-3">
			<div class="card-body">
					<?php
					$actualGloves = $gloves[0];
					if ($selectedGloves != null) {
						foreach ($gloves as $glove) {
							if ($selectedGloves[0]['glove'] == $glove['weapon_name']) {
								$actualGloves = $glove;
								break;
							}
						}
					}

					echo "<div class='card-header'>";
					echo "<h6 class='card-title item-name'>手套类型</h6>";
					echo "<h5 class='card-title item-name'>{$actualGloves["paint_name"]}</h5>";
					echo "</div>";
					echo "<img src='{$actualGloves["image_url"]}' class='weapon-image'>";
					?>
				</div>
				<div class="card-footer">
					<form action="" method="POST">
						<select name="forma" class="form-control select" onchange="this.form.submit()" class="SelectWeapon">
							<option disabled>选择手套</option>
							<?php
							foreach ($gloves as $gloveKey => $glove) {
								if ($selectedGloves[0]['glove'] == $glove['paint_name'])
									echo "<option selected value=\"glove-{$gloveKey}\">{$glove['paint_name']}</option>";
								else
									echo "<option value=\"glove-{$gloveKey}\">{$glove['paint_name']}</option>";
							}
							?>
						</select>
					</form>
				</div>
			</div>
		</div>
		

		<?php
		foreach ($weapons as $defindex => $default) { ?>
			<div class="col-sm-2">
				<div class="card text-center mb-3">
					<div class="card-body">
						<?php
						if (array_key_exists($defindex, $selectedSkins)) {
							echo "<div class='card-header'>";
							echo "<h5 class='card-title item-name'>{$skins[$defindex][$selectedSkins[$defindex]['weapon_paint_id']]["paint_name"]}</h5>";
							echo "</div>";
							echo "<img src='{$skins[$defindex][$selectedSkins[$defindex]['weapon_paint_id']]['image_url']}' class='skin-image'>";
						} else {
							echo "<div class='card-header'>";
							echo "<h5 class='card-title item-name'>{$default["paint_name"]}</h5>";
							echo "</div>";
							echo "<img src='{$default["image_url"]}' class='skin-image'>";
						}
						?>
					</div>
					<div class="card-footer">
						<form action="" method="POST">
							<select name="forma" class="form-control select" onchange="this.form.submit()" class="SelectWeapon">
								<option disabled>选择皮肤</option>
								<?php
								foreach ($skins[$defindex] as $paintKey => $paint) {
									if (array_key_exists($defindex, $selectedSkins) && $selectedSkins[$defindex]['weapon_paint_id'] == $paintKey)
										echo "<option selected value=\"{$defindex}-{$paintKey}\">{$paint['paint_name']}</option>";
									else
										echo "<option value=\"{$defindex}-{$paintKey}\">{$paint['paint_name']}</option>";
								}
								?>
							</select>
							<br></br>
							<?php
							$selectedSkinInfo = isset($selectedSkins[$defindex]) ? $selectedSkins[$defindex] : null;
							$steamid = $_SESSION['steamid'];

							if ($selectedSkinInfo):
								?>
								<button type="button" class="btn btn-primary" data-toggle="modal"
									data-target="#weaponModal<?php echo $defindex ?>">
									磨损模版设置
								</button>
							<?php else: ?>
								<button type="button" class="btn btn-primary" onclick="showSkinSelectionAlert()">
									磨损模版设置
								</button>
								<script>
									function showSkinSelectionAlert() {
										alert("请先选择皮肤再修改模版^");
									}
								</script>
							<?php endif; ?>

					</div>

					<?php
					// wear value 
					$selectedSkinInfo = isset($selectedSkins[$defindex]['weapon_paint_id']) ? $selectedSkins[$defindex] : null;
					$queryWear = $selectedSkins[$defindex]['weapon_wear'] ?? 1.0;
					// 初始磨损值
					$initialWearValue = isset($selectedSkinInfo['weapon_wear']) ? $selectedSkinInfo['weapon_wear'] : (isset($queryWear[0]['weapon_wear']) ? $queryWear[0] : 0.0);

					// 种子值
					$querySeed = $selectedSkins[$defindex]['weapon_seed'] ?? 0;
					$initialSeedValue = isset($selectedSkinInfo['weapon_seed']) ? $selectedSkinInfo['weapon_seed'] : 0;
					?>

					<div class="modal fade" id="weaponModal<?php echo $defindex ?>" tabindex="-1" role="dialog"
						aria-labelledby="weaponModalLabel<?php echo $defindex ?>" aria-hidden="true">
						<div class="modal-dialog" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class='card-title item-name'>
										<?php
										// 如果选中的皮肤在数组中存在，则显示相应的设置，否则显示默认设置
										if (array_key_exists($defindex, $selectedSkins)) {
											echo "{$skins[$defindex][$selectedSkins[$defindex]['weapon_paint_id']]["paint_name"]} 设置";
										} else {
											echo "{$default["paint_name"]} 设置";
										}
										?>
									</h5>
								</div>
								<div class="modal-body">
									<div class="form-group">
										<select class="form-select" id="wearSelect<?php echo $defindex ?>" name="wearSelect"
											onchange="updateWearValue<?php echo $defindex ?>(this.value)">
											<option disabled>耐久</option>
											<option value="0.00" <?php echo ($initialWearValue == 0.00) ? 'selected' : ''; ?>>
												0.00崭新</option>
											<option value="0.07" <?php echo ($initialWearValue == 0.07) ? 'selected' : ''; ?>>略磨
											</option>
											<option value="0.15" <?php echo ($initialWearValue == 0.15) ? 'selected' : ''; ?>>酒精
											</option>
											<option value="0.38" <?php echo ($initialWearValue == 0.38) ? 'selected' : ''; ?>>破败
											</option>
											<option value="0.45" <?php echo ($initialWearValue == 0.45) ? 'selected' : ''; ?>>战痕
											</option>
										</select>
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="wear">磨损</label>
												<input type="text" value="<?php echo $initialWearValue; ?>" class="form-control"
													id="wear<?php echo $defindex ?>" name="wear">
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="seed">模版</label>
												<input type="text" value="<?php echo $initialSeedValue; ?>" class="form-control"
													id="seed<?php echo $defindex ?>" name="seed" oninput="validateSeed(this)">
											</div>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
									<button type="submit" class="btn btn-danger">确定</button>
									</form>
								</div>
							</div>
						</div>
					</div>
					</div>
					</div>
					<script>
						// 更新磨损值
						function updateWearValue<?php echo $defindex ?>(selectedValue) {
							var wearInputElement = document.getElementById("wear<?php echo $defindex ?>");
							wearInputElement.value = selectedValue;
						}

						// 验证磨损值
						function validateWear(inputElement) {
							inputElement.value = inputElement.value.replace(/[^0-9]/g, '');
						}
						// 验证种子值
						function validateSeed(input) {
							// 检查输入的值
							var inputValue = input.value.replace(/[^0-9]/g, ''); // 只获取数字

							if (inputValue === "") {
								input.value = 0; // 如果为空或没有数字，则设置为0
							} else {
								var numericValue = parseInt(inputValue);
								numericValue = Math.min(1000, Math.max(1, numericValue)); // 控制区间

								input.value = numericValue;
							}
						}
					</script>
					<?php } ?>
					<?php } ?>
					</div>
					</div>
					<div class="container">
						<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top">
							<div class="col-md-4 d-flex align-items-center">
								<span class="mb-3 mb-md-0 text-body-secondary">© 2024 <a href="https://yumi1.top">yumi1.top</a></span>
							</div>
						</footer>
					</div>
					</body>

					</html>
