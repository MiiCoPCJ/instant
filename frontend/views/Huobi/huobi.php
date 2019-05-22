<div>
	<div>huobi</div>
</div>

<script>
 ws = new WebSocket('wss://api.huobi.pro/ws');
 ws.onopen = function (ev) {
		 console.log('onopen');
		 console.log(ev);
 };
 ws.onmessage = function (ev) {
		 console.log('onmessage');
		 console.log(ev);
 }

 ws.onclose = function (ev) {
		 console.log('onclose');
		 console.log(ev);
 };
 ws.onerror = function (ev) {
		 console.log('onerror');
		 console.log(ev);
 };
</script>
