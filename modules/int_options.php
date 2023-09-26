<?
global $isSemanticUrl;
global $urlArrf;
//тут будем собирать массив $filterCheck, состоящий из uID фильтров, которые можно отображать
$filterCheck=array();
$filterTarget='all';
if ($isSemanticUrl) {
	$urlArrfl = $urlArrf;
	$cat = array_shift($urlArrfl);
	if ($cat != 'catalog') array_unshift($urlArrfl, $cat);
	if (count($urlArrfl)!=0) {
		$cat =	$category->getlastcat($urlArrfl);

		/**/
		$catss = $category->getallhierarchycatuids($category::$catarr, $cat['uID']);
		$catss = implode("','", $catss);

		$query="SELECT rpv.vuID FROM relProductsToValues rpv WHERE rpv.puID IN
		( SELECT p.uID FROM products p WHERE p.parent IN ('".$catss."') AND p.active=1 )
		AND rpv.active=1
		GROUP BY rpv.vuID";
		$productsPrepare=$go->getAll($query);
		foreach ($productsPrepare as $row) array_push($filterCheck,$row['vuID']);

		$filterTarget='sg';
	} else {
		$query='SELECT pv.uID FROM propertiesValues pv WHERE pv.active=1';
		$productsPrepare=$go->getCol($query);
		foreach ($productsPrepare as $row) array_push($filterCheck,$row);
		$filterTarget='All';
	}
	/*
	echo '<pre>';
	var_dump($catss);
	echo '</pre>';
	/**/
} else {
	if((array_key_exists('level', $_GET) AND $urlArr[1]=='catalog' && $_GET['target']=='products' && $_GET['level']=='1' && isset($_GET['id'])) || (array_key_exists('page', $_GET) AND $_GET['page']=='mcatalog' && $_GET['target']=='products' && isset($_GET['id']))) {
		$query='SELECT rpv.vuID FROM relProductsToValues rpv WHERE rpv.puID IN
		( SELECT p.uID FROM products p, subgroups sg, groups g WHERE p.parent=sg.uID AND sg.parent=g.uID AND g.ID='.$_GET['id'].' AND p.active=1 )
		AND rpv.active=1
		GROUP BY rpv.vuID';
	    $productsPrepare=$go->getAll($query);
	    foreach ($productsPrepare as $row) array_push($filterCheck,$row['vuID']);
		$filterTarget='g';
	}
	//по подгруппам
	elseif (array_key_exists('level', $_GET) AND $urlArr[1]=='catalog' && $_GET['target']=='products' && $_GET['level']=='2' && isset($_GET['id'])) {
	    $query='SELECT rpv.vuID FROM relProductsToValues rpv WHERE rpv.puID IN
	    ( SELECT p.uID FROM products p, subgroups sg WHERE p.parent=sg.uID AND sg.ID='.$_GET['id'].' AND p.active=1 )
	    AND rpv.active=1
	    GROUP BY rpv.vuID';
	    $productsPrepare=$go->getAll($query);
	    foreach ($productsPrepare as $row) array_push($filterCheck,$row['vuID']);
	    $filterTarget='sg';
	}
	/*elseif($_GET['page']=='mcatalog' && $_GET['target']=='products' && isset($_GET['id'])) {
	    $query='SELECT rpv.vuID FROM relProductsToValues rpv WHERE rpv.puID IN
		( SELECT p.uID FROM products p, subgroups sg, groups g WHERE p.parent=sg.uID AND sg.parent=g.uID AND g.ID='.$_GET['id'].' AND p.active=1 )
		AND rpv.active=1
		GROUP BY rpv.vuID';
	    $productsPrepare=$go->getAll($query);
	    foreach ($productsPrepare as $row) array_push($filterCheck,$row['vuID']);
		$filterTarget='g';
	    }*/
	//по назначениям elseif($_GET['target']=='uses' && $_GET['level']=='1' && isset($_GET['id'])) $pquery='SELECT p.* FROM products p WHERE active=1';
	//по группам назначений elseif($_GET['target']=='uses' && $_GET['level']=='2' && isset($_GET['id'])) $pquery='SELECT p.* FROM products p WHERE active=1';
	//стандартный запрос (выводим все с showOnMain=1)
	else {
	    /*$query='SELECT pv.uID FROM relPropertiesToValues rpv, properties p, propertiesValues pv
	WHERE rpv.active=1
	AND p.active=1
	AND pv.active=1
	AND p.showOnMain=1
	AND rpv.vuID=pv.uID
	AND rpv.puID=p.uID';*/
	    $query='SELECT pv.uID FROM propertiesValues pv WHERE pv.active=1';
	    $productsPrepare=$go->getCol($query);
	    foreach ($productsPrepare as $row) array_push($filterCheck,$row);
	    $filterTarget='all';
	}
}
//достаем список значений свойств + имена и данные свойств

$properties=$go->getAll("
SELECT pv.name,pv.ID pvID,pv.uID,rpv.puID,p.name vname,p.ID pID,p.showOnMain som
FROM propertiesValues pv,relPropertiesToValues rpv,properties p
WHERE pv.active=1 AND rpv.vuID=pv.uID AND rpv.puID=p.uID AND rpv.active=1 AND p.active=1
GROUP BY pv.uID
ORDER BY p.route, p.name, pv.name");

$prepare=array();
$options=array();
$flag=0;
//собираем двухуровневый массив из инфы и сортируем значения свойств
foreach($properties as $key=>$val) {
    if(in_array($val['uID'],$filterCheck,TRUE)) {
        if ($flag!=$val['pID']) {
            $prepare=array();
            $flag=$val['pID'];
            }
        $prepare[$val['pvID']]=$val['name'];
        $options[$flag]['name']=$val['vname'];
        $options[$flag]['pID']=$val['pID'];
        $options[$flag]['show']=$val['som'];
        natcasesort($prepare);
        $options[$flag]['list']=$prepare;
    }
}

//print_r($options);
//собираем верстку

if (!isset($filters)) $filters = array();

if ($options) {
    $optionsShow=array();
    array_push($optionsShow,'<div class="optovhwrap" style="overflow-y:auto;max-height:70vh;scrollbar-width:thin;scrollbar-color:#b2b4b3 #f2f6f7;"><div class="optionsactive"></div>');
    if ($filterTarget=='all') { //ненужные фильтры скрываем
        foreach ($options as $key=>$prop) {
            /*if (in_array($val['uID'],$filterCheck,TRUE)) $visible='';
            else $visible=' style="display:none;"';*/
            if ($options[$prop['pID']]['show']==0) {$visible=' style="display:none;"';$basicShow=0;}
            else {$visible='';$basicShow=1;}
        array_push($optionsShow,'<div class="optionsListRow" id="'.$prop['pID'].'" basicshow="'.$basicShow.'"'.$visible.'><div class="optionsListRowText">'.$prop['name'].'</div><div class="subrowsContainer">');
        foreach ($prop['list'] as $vID=>$vname) {
            if (in_array($vID,$filters)) $selected=' selectedSubrow';
            else $selected='';
            array_push($optionsShow,'<div class="optionsListSubrow'.$selected.'" propid="'.$vID.'" available="1">'.$vname.'</div>');
            }
        array_push($optionsShow,'</div></div>');
        }

    }
    else { //ненужные фильтры не выводим вообще
        foreach ($options as $key=>$prop) {
        array_push($optionsShow,'<div class="optionsListRow" id="'.$prop['pID'].'" basicshow="1"><div class="optionsListRowText">'.$prop['name'].'</div><div class="subrowsContainer">');
        foreach ($prop['list'] as $vID=>$vname) {
            if (in_array($vID,$filters)) $selected=' selectedSubrow';
            else $selected='';
            array_push($optionsShow,'<div class="optionsListSubrow'.$selected.'" propid="'.$vID.'" available="1">'.$vname.'</div>');
            }
        array_push($optionsShow,'</div></div>');
        }
    }

    array_push($optionsShow,'</div>');
		//echo "<div class='optionsactive'></div>";
    foreach ($optionsShow as $val) echo $val;

	}
	else echo '<div class="text-center">Нет подходящих параметров.</div>';
?>

<button id="applyFilters" class="greenGradient">Применить фильтры</button>
<button id="clearFilters" class="greyGradient">Сбросить</button>

<script>
$(document).ready(function (){
    $('.selectedSubrow').closest('.optionsListRow').addClass('optionsListRowOpened');
    $('.selectedSubrow').parent('.subrowsContainer').show();
    findFiltersCross();
    //setInterval(function(){findEmptyOptionsContainer();}, 500);

    $('#clearFilters').click(function (){
        $('.selectedSubrow').removeClass('selectedSubrow');
        if(AFSC()) {
            findFiltersCross();
            }
        else {
            console.log('Нет выбранных');
            $('.optionsListSubrow').removeClass('hide').attr('available','1');
            $('.optionsListRow[basicshow=0]').addClass('hide');
            $('.optionsListRow[basicshow=1]').removeClass('hide');
            }
        });

	$('.optionsListRowText').click(function (){
		$(this).next('.subrowsContainer').toggle(200);
		$(this).parent('.optionsListRow').toggleClass('optionsListRowOpened');
		});

	$('.optionsListSubrow').click(function () {
			var jt = jQuery(this);

	    if(!jt.hasClass('selectedSubrow')) {
					console.log('Set selectedSubrow');
	        //$(this).parent('.subrowsContainer').children('.optionsListSubrow').removeClass('selectedSubrow');
	        jt.addClass('selectedSubrow');
					console.log("jQuery(this).closest('.optionsListRow')=", jt.closest('.optionsListRow'));
					if (!chkselthis(this)) {
						jt.attr( "do-not-hide", "1" );
						if (!chkselexist()) // если первое добавилось, не прячем варианты
							jt.siblings().attr( "do-not-hide", "1" );
						else { // а тут оставим из текущего только те, что показывались
							jt.siblings('[available=1]').attr( "do-not-hide", "1" );
						}
						//jt.removeAttr( "do-not-hide"); это позволяет скрыть несуществующее, если выбраны ниже свойства не пересекаются с верхними
						// вернуть правда пока сложно, т.к. в ответе сервера этого варианта не вернётся.
						jQuery('.optionsList .optionsactive').append(jt.closest('.optionsListRow'));
					} else {
					}
	    } else {
				//if (jQuery(this).parent('.optionsactive'))
				//значит выбраный снимается и ниже надо сбросить.
				jt.removeClass('selectedSubrow');

				if (chkselthis(this) && !chksellist(this)) {
					jt.removeAttr( "do-not-hide");
					jt.siblings().removeAttr( "do-not-hide");
					let jtp = jt.closest('.optionsListRow');
					jtp.removeClass('optionsListRowOpened');
					jtp.find('.subrowsContainer').hide();
					jQuery('.optionsList .optovhwrap>.optionsListRow').last().after(jtp);//after .optionsactive
				}
			}

			// изменения, значит для всех, что ниже при изменении дать возможность изменить варианты, но пока просто сбрасываем
			jt.closest('.optionsListRow').nextAll().each(function(){
				var jlt = jQuery(this);
				//jlt.attr( "do-not-hide-remove", "1");
				//console.log ("jlt.find('.optionsListSubrow')=", jlt.find('.optionsListSubrow'));
				jlt.find('.optionsListSubrow').removeAttr( "do-not-hide").removeClass('selectedSubrow');
				//jlt.removeClass('optionsListRowOpened');
				jQuery('.optionsList .optovhwrap>.optionsListRow').last().after(jlt);
			});

      if(AFSC()) {
					console.log('111');
          findFiltersCross();
      } else {
            console.log('Нет выбранных');
            $('.optionsListSubrow').removeClass('hide').attr('available','1');
						if (true) $('.optionsListRow').removeClass('hide');
      }
	});

	$('#applyFilters').click(function (){
	    filters=[];
	    basicLink=$('#settings').attr('basiclink');
	    if (basicLink=='0') basicLink='/catalog/?'; ///catalog?target=products

	    $('.selectedSubrow').each(function(){filters.push($(this).attr('propid'));});

	    if (filters.length!=0) {
	        basicLink+='&filters=';
	        for (var i=0; i<filters.length; i++) if (i==0) basicLink+=filters[i]; else basicLink+=','+filters[i];
	        }

	    location.href = basicLink;
	    });
	});

// Create Base64 Object
var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/\r\n/g,"\n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}


function findFiltersCross(){
		filtersStr='filters=';
		var jt = jQuery(this);
		$('.selectedSubrow').each(function(){filtersStr=filtersStr+','+jQuery(this).attr('propid');});

		//$('.optionsListSubrow').removeAttr( "do-not-hide");
		//$('.selectedSubrow').siblings().attr( "do-not-hide", "1" );

  	filtersStr = filtersStr + '&url=' + Base64.encode(document.URL) + '';
		var catselrcted = jQuery('.maincatwrp .cItemOpened');
		let gStr = '';
		if (catselrcted.length>0) {
			gStr = 'g=';
			let arr = new Array();
			catselrcted.each(function(index) {
				arr.push(jQuery(this).attr('g'));
			});
			gStr = gStr + arr.join(',');
		}
		if (gStr!='') filtersStr = filtersStr + '&' + gStr;
		console.log(filtersStr);
		$.ajax({
			type: "GET",
			dataType: 'json',
			//processData: false,
			//contentType: false,
			cache: false,
			url: "/ajax/ajax_filters.php",
			data: filtersStr
			})
			.done(function(result) {
				if (result.status==1) {
				optionsArr=result.content;
				$('.optionsListSubrow').each(function (){
				    if (!$(this).attr('do-not-hide') && optionsArr.indexOf($(this).attr('propid'))==-1) $(this).addClass('hide').removeClass('selectedSubrow').attr('available','0');
				    else $(this).removeClass('hide').attr('available','1');
				    });
				}
				findEmptyOptionsContainer('');
			});
    }

function findEmptyOptionsContainer (hcls){
    $('.subrowsContainer').each(function (){
        if ($(this).children('[available=1]').length==0) $(this).parent('.optionsListRow').addClass('hide'+' '+hcls);
        else {
            $(this).parent('.optionsListRow').removeClass('hide'+' '+hcls)
            $(this).children('.optionsListSubrow[available=1]').removeClass('hide'+' '+hcls);
            }
        });
    }

function AFSC (){
 if ($('.selectedSubrow').length>0) return true;
 else return false;
}

function chkselthis(that) {
	//console.log("jQuery(that).parent('.optionsactive')=", jQuery(that).parent('.optionsactive'));
	if (jQuery(that).closest('.optionsactive').length>0) return true;
	else return false;
}

function chkselexist() {
	if (jQuery('.optionsList .optionsactive .optionsListRow').length>0) return true;
	else return false;
}

function chksellist(that) {
	//console.log("jQuery(that).parent('.optionsactive')=", jQuery(that).parent('.optionsactive'));
	if (jQuery(that).closest('.optionsListRow').find('.selectedSubrow').length>0) return true;
	else return false;
}

</script>
