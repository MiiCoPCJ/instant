<script src="/js/jquery.min.js"></script>

<div>
	<div>okex</div>
</div>

<script>

	 $.ajax({
       type : "get",
       url  : "http://localhost:876",
       data : {
       },
       success : function(e){
				 console.log('success');
         console.log(e);
       },
       error : function(e){
				 console.log('error');
         console.log(e);
       }
	});

</script>
