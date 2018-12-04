<?php 
session_start();
//New Game start
if($_GET['param']=="new_game"){
	new_game();
}

//If it is not a new game and increase the counter
if($_GET['param']!="new_game" && isset($_SESSION['feed_count'])){
	$_SESSION['feed_count']=$_SESSION['feed_count']+1;
}

//Condition to check if the turn is 50 and fetch result
if($_SESSION['feed_count']==50){
	$result=result();	
}else{
	if($_GET['param']!="new_game"){
		//If not initialized the variables redirect to home page to start a new game
		if(!isset($_SESSION['feed_count'])){	
			header("Location: index.php"); exit;
		}

		//Initialize session variables once the game is started and counter increased
		if(!isset($_SESSION['old_array']) || empty($_SESSION['old_array'])){
			$old_array=$_SESSION['updated_array'];
			$_SESSION['old_array']=$old_array;
			$new_array=array();
			$_SESSION['new_array']=$new_array;
		}else{
			$old_array=$_SESSION['old_array'];
		}

		//Fetch random value from the array and feed
		$fetch_random_value=array_rand($_SESSION['old_array']);
		$fed = $_SESSION['updated_array'][$fetch_random_value];

		//Array to store the one's that are fed
		$new_array=$_SESSION['new_array'];
		if(count($new_array)<=count($_SESSION['updated_array'])){			
			array_push($new_array,$fed);
			$_SESSION['new_array']=$new_array;
		}else{
			unset($_SESSION['new_array']);
		}	

		//Move the fed one to new array and unset from old
		unset($old_array[$fetch_random_value]);
		$_SESSION['old_array']=$old_array;

		//Increase the counter for one that is fed
		$_SESSION['counter']=counter_feed($fetch_random_value,$_SESSION['counter']);

		//Check if the counter by when they should be fed has not been increased
		$counter_removed=check_counter_increase($fetch_random_value,$_SESSION['counter']);
		if(!empty($counter_removed)){
			foreach($counter_removed as $key=>$value){
				$_SESSION['removed']=$_SESSION['updated_array'][$key];
				unset($_SESSION['updated_array'][$key]);
				unset($_SESSION['old_array'][$key]);
				unset($_SESSION['counter'][$key]);
				$_SESSION['removed_key']=$key;
			}
		}

		//Display result if any of the turn has exceeded for feeding
		$result=result();
	}
}

//Destroy sessions and game over
function game_over(){
	session_destroy();
}

//Inititalize the game
function new_game(){
	$_SESSION['feed_count']=0;
	$_SESSION['updated_array']=array(1=>'Farmer', 2=>'Cow1',3=>'Cow2', 4=>'Bunny1',5=>'Bunny2',6=>'Bunny3',7=>'Bunny4');
	$_SESSION['counter']=array(1=>0, 2=>0,3=>0, 4=>0,5=>0,6=>0,7=>0);	
}

//Function to update feed as per turn
function counter_feed($new_feed,$count_session){
	if(isset($count_session) && !empty($count_session)){
		foreach($count_session as &$element) {
			$element++;
		}
	}
	if($new_feed!=''){
		$count_session[$new_feed]=0;
	}
	return $count_session;
}

//Function to check the turn to feed has not been increased
function check_counter_increase($new_feed,$count_session){
	$max_count_array=array(1=>15,2=>10,3=>10,4=>8,5=>8,6=>8,7=>8);
	$result_array=array_intersect_assoc($max_count_array,$count_session);
	return $result_array;
}

//Check if list of key exist in array
function check_key_exists($keys, $arr) {
	return array_diff_key(array_flip($keys), $arr);
}

//Display the result based on count in array
function result(){
	//Check if farmer key exist in array
	$count_farmer=check_key_exists(array(1),$_SESSION['updated_array']);

	//check key for cows if it exist in array
	$count_cow=check_key_exists(array(2,3),$_SESSION['updated_array']);

	//check key for bunny if it exist in array
	$count_bunny=check_key_exists(array(4,5,6,7),$_SESSION['updated_array']);

	if(count($farmer)==1 || count($count_cow)==2 || count($count_bunny)==4){
		return 2;
	}else{
		return 1;
	}	
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>FarmGame</title>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta charset="utf-8">
		<meta name="keywords" content="Farm Game" />	
		<link href="https://demo.w3layouts.com/demos_new/template_demo/03-09-2018/arable_demo_Free/1791219992/web/css/bootstrap.css" rel='stylesheet' type='text/css' />
		<link href="https://demo.w3layouts.com/demos_new/template_demo/03-09-2018/arable_demo_Free/1791219992/web/css/style.css" rel='stylesheet' type='text/css' />
		<link href="https://demo.w3layouts.com/demos_new/template_demo/03-09-2018/arable_demo_Free/1791219992/web/css/minimal-slider.css" rel='stylesheet' type='text/css' />
		<link href="//fonts.googleapis.com/css?family=Josefin+Sans:100,100i,300,300i,400,400i,600,600i,700,700i" rel="stylesheet">
		<link href="//fonts.googleapis.com/css?family=BenchNine:300,400,700" rel="stylesheet">
	</head>
	<body>
		<div class="slide-window">
			<div class="slide-wrapper" style="width:100%;">
				<div class="slide slide3">
					<div class="slide-caption text-center">
						<p class="text-uppercase">
							<?php 
							//Display turn counter
							if(isset($_SESSION['feed_count']) && ($_SESSION['feed_count']!=0)){
								echo 'Feed '.$_SESSION['feed_count']. '-'.$fed.' is fed';
							} 
							//Display who is fed
							if(isset($_SESSION['removed']) && !empty($_SESSION['removed'])){
								echo '<br/>'.$_SESSION['removed'] . ' died as not fed on time'; 
								unset($_SESSION['removed']);
							}?>								
						</p>
						<h4>
							<!--Display list of pending farmer and animals-->
							<?php if(isset($_SESSION['updated_array']) && !empty($_SESSION['updated_array'])){
								$last_element_array=end($_SESSION['updated_array']);
								echo 'Pending: ';
								foreach($_SESSION['updated_array'] as $key_array=>$value){
									echo $value;
									if($value!=($last_element_array)){
										echo ',';
									}
								}
							} ?>
						</h4>
						<div class="cont-btn">
							<!--Display feed button if the turn is not 50 and farmer has not died or one farmer, one cow and one bunny not present in final array to win the game-->
							<?php if((!isset($_SESSION['feed_count']) || $_SESSION['feed_count']!=50) && $_SESSION['removed_key']!=1 && $result!=2){?>
								<a class="btn text-uppercase" href="new_game.php">Feed</a>
							<?php } 
							$game_over_text='<br/>
									<a href="new_game.php?param=new_game">Start a New game</a>';

							if($_GET['param']!="new_game"){
								//Based on final result
								if($result==2){
									echo '<h3>You Lose the Game</h3>'.$game_over_text;
									game_over();
								}
								//If turns completed and one farmer, one cow and one bunny remaining, you win the game
								if($_SESSION['feed_count']==50){
									if($result!=2){
										echo '<h3>Congratulations!! You Win the Game.</h3>'.$game_over_text;
										game_over();
									}
								}
								//You lose the game if atleast 1 of each is not pending
								if($_SESSION['removed_key']==1){
									echo '<h3>Game Over. You Lose the Game.</h3>'.$game_over_text;
									game_over();
								}
							}?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
</html>