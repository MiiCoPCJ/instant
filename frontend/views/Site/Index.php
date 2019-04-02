<div>
	<div>Hello World</div>
</div>

<div>
	<div>ViewPartial:</div>
	<div><?= $this->ViewPartial('/Common/Test', ["par" => "123"]) ?></div>
</div>

<div>
	<div>ViewComponent:</div>
	<div><?= $this->ViewComponent('Test', ["par" => "456"]) ?></div>
</div>
