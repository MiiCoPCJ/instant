<script src="/js/jquery.min.js"></script>
<script type="text/javascript" src="/js/highstock.js"></script>
<link rel="stylesheet" type="text/css" href="/css/style.css">

<div class="wrap">
	<div id="container" style="height: 100%"></div>
</div>

<script type="text/javascript">
// $.ajax({
// 		type : "get",
// 		url  : "http://45.77.77.132:876/request",
// 		data : {
// 		},
// 		success : function(e){
// 			console.log(e);
// 		},
// 		error : function(e){
// 				console.log(e);
// 		}
// })

ws = new WebSocket('wss://real.okex.com:10442/ws/v3');
    ws.onopen = function (ev) {
        ws.send('{"op": "subscribe", "args": ["spot/candle3600s:BTC-USDT"]}')
    };
    ws.onmessage = function (ev) {
        console.log('onmessage');
        console.log(ev);
    }

    ws.onclose = function () {
        console.log('onclose')
    };
    ws.onerror = function () {
        console.log('onerror')
    };

	// Create the chart
	// Highcharts.stockChart('container', {
	//     chart: {
	//         events: {
	//             load: function () {
	//
	//                 // set up the updating of the chart each second
	//                 // var series = this.series[0];
	//                 // setInterval(function () {
	//                 //     var x = (new Date()).getTime(), // current time
	//                 //         y = Math.round(Math.random() * 100);
	//                 //     series.addPoint([x, y], true, true);
	//                 // }, 1000);
	// 								var series = this.series[0];
	// 								ws.onmessage = function (ev) {
	// 										var x = (new Date()).getTime();
	// 						        console.log(ev.data);
	// 										var y = parseInt(ev.data);
	// 										series.addPoint([x, y], true, true);
	// 						    }
	//             }
	//         }
	//     },
	//
	//     time: {
	//         useUTC: false
	//     },
	//
	//     rangeSelector: {
	//         buttons: [{
	//             count: 1,
	//             type: 'minute',
	//             text: '1M'
	//         }, {
	//             count: 5,
	//             type: 'minute',
	//             text: '5M'
	//         }, {
	//             type: 'all',
	//             text: 'All'
	//         }],
	//         inputEnabled: false,
	//         selected: 0
	//     },
	//
	//     title: {
	//         text: 'Live random data'
	//     },
	//
	//     exporting: {
	//         enabled: false
	//     },
	//
	//     series: [{
	//         name: 'Random data',
	//         data: (function () {
	//             // generate an array of random data
	//             var data = [],
	//                 time = (new Date()).getTime(),
	//                 i;
	//
	//             for (i = -999; i <= 0; i += 1) {
	//                 data.push([
	//                     time + i * 1000,
	//                     Math.round(Math.random() * 100)
	//                 ]);
	//             }
	//             return data;
	//         }())
	//     }]
	// });

</script>
