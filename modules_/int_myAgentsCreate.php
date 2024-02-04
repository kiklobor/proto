<div class="agentsWrapper">
<form id="makeAgents" method="post">
<h1>Создание контрагента.</h1>
<span>
</span>
<div class="agentTypeWrapper">
Выберите тип контрагента: 
<label id="agentLabel1" class="agentTypeLabel selected"><input type="radio" name="agentType" value="1" style="display:none;" checked>Физическое лицо</label>
<label id="agentLabel2" class="agentTypeLabel"><input type="radio" name="agentType" value="2"style="display:none;">Юридическое лицо / Индивидуальный предприниматель</label>
</div>

<div class="agents1">
<input class="cartInput" type="text" form="makeAgents" name="name" placeholder="Имя"><br>
<input class="cartInput" type="text" form="makeAgents" name="phone" placeholder="Телефон"><br>
<input class="cartInput" type="text" form="makeAgents" name="mail" placeholder="Электронная почта"><br>
</div>

<div class="agents2">
<input class="cartInput" type="text" form="makeAgents" name="orgname" placeholder="Название организации"><br>
<input class="cartInput" type="text" form="makeAgents" name="orgphone" placeholder="Телефон"> Вы можете указать любое количество телефонов через запятую.<br>
<input class="cartInput" type="text" form="makeAgents" name="orgmail" placeholder="Электронная почта"><br>
<input class="cartInput" type="text" form="makeAgents" name="inn" placeholder="ИНН"><br>
<input class="cartInput" type="text" form="makeAgents" name="kpp" placeholder="КПП"><br>
<input class="cartInput" type="text" form="makeAgents" name="ogrn" placeholder="ОГРН"><br>
<input class="cartInput" type="text" form="makeAgents" name="okpo" placeholder="ОКПО"><br>
<input class="cartInput" type="text" form="makeAgents" name="bank" placeholder="Банк"><br>
<input class="cartInput" type="text" form="makeAgents" name="bik" placeholder="БИК"><br>
<input class="cartInput" type="text" form="makeAgents" name="rassch" placeholder="Расчетный счет"><br>
<input class="cartInput" type="text" form="makeAgents" name="korsch" placeholder="Корреспонденсткий счет"><br>
</div>

<input type="hidden" name="action" value="createAgent" form="makeAgents">
<div class="cartFinal">
<a class="textlink goback">Назад</a> <button class="greenGradient makeAgentsButton" type="submit">Сохранить контрагента</button>
</div>
</div>
</form>

<script>
$('input[name=agentType]').change(function (){
		$('.agentTypeLabel').removeClass('selected');
		value=$(this).val();
		if(value==1) {
			$('.agents1').slideDown(300);
			$('.agents2').slideUp(300);
			$('#agentLabel1').addClass('selected');
			}
		else {
			$('.agents2').slideDown(300);
			$('.agents1').slideUp(300);
			$('#agentLabel2').addClass('selected');
			}
	});
</script>