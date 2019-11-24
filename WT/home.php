<!DOCTYPE html>
<html>
<head>
    <!-- <link rel="stylesheet" href="home.css"> -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" >
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> -->
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" type="text/css"
        rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css">
    <link href="home.css" type="text/css" rel="stylesheet">
<style>
		#container{
		border:solid 1px black;
		display:none
		}
		.fooditem{
		
			background-color:white;
			color:rgb(221, 28, 28);	
			font-size: 22px;
			font-weight:bolder;
			text-align: center;
			font-family: Verdana;
			text-transform: uppercase;
		}
		.fooditem:hover{
		
			background-color:white;
		}
		#search{
			
			height:40px;
			width:80%;
			/* width:430px; */
			background-color: rgb(243, 224, 227);
			font-size:20px;
			font-family: Verdana;
			border: none;
			border-bottom: 1px solid gray;
			margin-left:150px;
			text-align: center;
			
        	
		}
		
		</style>
</head>



<script>
	var cat;
		function Suggest(){
		//Maintain a reference to the current object
			var object=this;
			this.timer=null;
			this.search=null;
			this.container=null;
			this.url=null;
			this.xhr=new XMLHttpRequest();
			this.getTerm=function(){
				this.search=document.getElementById("search");
				this.container=document.getElementById("container");
			//to invoke the send search function only if the user
			//pauses for a second			
				if(this.timer){
					clearTimeout(this.timer);
				}
				this.timer=setTimeout(this.sendTerm,1000)
				
			
			}
			this.sendTerm=function(){
				object.url="suggest.php?term="+object.search.value;
				//check if the search term is available in cache
				// i.e. Local Storage
				if(localStorage.getItem(object.url)){
					var cacheRes=JSON.parse(localStorage.getItem(object.url))
					object.populateCategory(cacheRes);
					console.log(localStorage.getItem(object.url));
					console.log("from browser cache")
				}
				else{
				object.xhr.onreadystatechange=object.showResult;
				console.log(object.search.value)

				console.log(this)
				object.xhr.open("GET",object.url,true);
				object.xhr.send();
				}
			}
			this.showResult=function(){
				console.log("hi");
				if(this.readyState==4 && this.status==200){

					var res=this.responseText;
					var resO=JSON.parse(res);
					console.log(res);
					console.log("cat=",resO);
					cat = resO[0];
					xhr = new XMLHttpRequest();	
					if(xhr)
					{
	
						xhr.open("GET", "http://localhost:5000/api/user/search/product/category?prodCat="+resO[0]);
						xhr.onreadystatechange = handler;
						xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
						xhr.responseType="json";
						xhr.send(null);
					}
					//Store search result in cache
					localStorage.setItem(object.url,res);
					if(resO.length==0){
					
						//object.search.style.backgroundColor="red"
						object.search.style.fontStyle="italics"
					}
					else{
						object.populateCategory(resO);	
					}
				}

			}
			function handler(){
				if(xhr.readyState=="4"&&xhr.status==200){

					//localStorage.setItem("productId",result["productId"]);
				// var result= xhr.response;
				// localStorage.setItem("productName",result["productName"]);
				// localStorage.setItem("productPrice",result["productPrice"]);
				//console.log(productId);
				// console.log(productName);
				// console.log(productPrice);
					setTimeout(getImages,1000);	
			}
			}
			function getImages()
			 
			 {
				 xhr = new XMLHttpRequest();	
			 if(xhr)
			 {
	 
			xhr.open("GET", "http://localhost:5000/api/user/search/product/category?prodCat="+cat);
			 xhr.onreadystatechange = showImg;
			 xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
			 xhr.responseType="json";
			 xhr.send(null);
		 }
			 }
		 
		 function showImg()
		 {
			 if(xhr.readyState == 4 && xhr.status == 200)
			 {
				 var txt="";
				 var result= xhr.response;
				 console.log(result);
				 txt+='<div class="gallery">';
				 for(i in result){
 							console.log("i=",i)
						 txt+='<button class="box" type="submit" name=sel id='+i+' value='+result[i].productId+' onclick="details('+i+')">';
			 		 	// txt+='<button style="background:white; border:6px solid red; height=400px; width=500px;" type="submit" name=sel value='+result[i].productId+'onclick="display()">';
						txt += "<img src='Pictures/"+result[i].productImage+"' height='250px' width='250px'>";
					
			 		 	txt += '<div class="desc"><strong>'+result[i].productName+'</strong></br></br>'+'<b>$</b>'+result[i].productPrice+'</div>';
			 		 	
						txt+='</button>';
						// txt+='</br><button id="add">';
						// 		txt+='<ul>';
						// 				txt+='<li><a class="sub" href="#about">ADD TO CART</a></li>';
						// 				txt+='</ul>';
						// 				txt+='</button>';
						   
				 }
				 txt+='</div>';
				 document.getElementById("main4").innerHTML = txt;	
			 }
			 
			 
		 }
		 function details(pid){
			console.log("pid=",pid.id);
			localStorage.setItem("productId",pid.id);
			// this.xhr.onreadystatechange=obj.send;
			// this.xhr.open("GET","http://localhost:5000/api/user/product/details?prodId="+pid.id);
			// this.xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
			// this.xhr.responseType="json";
			// this.xhr.send();
			window.location.href="productdetails_1.html";
		}
			this.populateCategory=function(resO){
				object.container.innerHTML="";
				for(f in resO){
					
					var itemDiv=document.createElement("div");
					itemDiv.innerHTML=resO[f];
					itemDiv.className="fooditem";
					itemDiv.onclick=object.setProduct;
					object.container.appendChild(itemDiv)
					console.log(object.container)
				}
				object.container.style.display="block";
				console.log(object.container)
			
			
			}
			this.setProduct=function(e){
				object.search.value=e.target.innerHTML;
				object.search.style.backgroundColor="white";
				object.container.style.display="none"
				object.container.innerHTML="";
			
			}
		
		
		
		}
		
		
		var obje=new Suggest();	

			function send(){
				xhr = new XMLHttpRequest();	
				if(xhr)
			{
				xhr.onreadystatechange = handler;
				xhr.open("GET", "http://localhost:5000/api/user/product");
				xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
			 	xhr.responseType="json";
				xhr.send();
				
			}
		}
			function handler(){
				if(xhr.readyState=="4"&&xhr.status==200){

					//localStorage.setItem("productId",result["productId"]);
				// var result= xhr.response;
				// localStorage.setItem("productName",result["productName"]);
				// localStorage.setItem("productPrice",result["productPrice"]);
				//console.log(productId);
				// console.log(productName);
				// console.log(productPrice);
					setTimeout(getImages,2000);	
			}
			}

			
		
		// 		if(xhr.readyState=="4"&&xhr.status==200){
		// 			var arr=xhr.response;
		// 			console.log(arr);
		// 			document.getElementById("img").innerHTML=arr[0];
		// 			setTimeout(getImages,2000);	
		//   }
		
		
		
				// xhr.onreadystatechange = handler;
				// xhr.open("GET", "http://localhost:5000/api/user/product");
				// this.xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
				// this.xhr.responseType="json";
				// this.xhr.send();
		
			
			 function getImages()
			 
			{
				xhr = new XMLHttpRequest();	
			if(xhr)
			{
	
			xhr.open("GET", "http://localhost:5000/api/user/product");
			xhr.onreadystatechange = showImg;
			xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
			xhr.responseType="json";
			xhr.send(null);
		}
			}
		
		function showImg()
		{
			if(xhr.readyState == 4 && xhr.status == 200)
			{
				var txt="";
				var result= xhr.response;
				console.log(result);
				txt+='<div class="gallery">';
				for(i in result){

						txt+='<button class="box" type="submit" name=sel id='+i+' value='+result[i].productId+' onclick="details('+i+')">';
			 		 	// txt+='<button style="background:white; border:6px solid red; height=400px; width=500px;" type="submit" name=sel value='+result[i].productId+'onclick="display()">';
						txt += "<img src='Pictures/"+result[i].productImage+"' height='250px' width='250px'>";
					
			 		 	txt += '<div class="desc"><strong>'+result[i].productName+'</strong></br></br>'+'<b>$</b>'+result[i].productPrice+'</div>';
			 		 	
						txt+='</button>';
						// txt+='</br><button id="add">';
						// 		txt+='<ul>';
						// 				txt+='<li><a class="sub" href="#about">ADD TO CART</a></li>';
						// 				txt+='</ul>';
						// 				txt+='</button>';
						  
				}
				txt+='</div>';
				document.getElementById("main4").innerHTML = txt;	
			}
			
			
		}
		function details(pid){
			console.log(pid.id);
			localStorage.setItem("productId",pid.id);
			// this.xhr.onreadystatechange=obj.send;
			// this.xhr.open("GET","http://localhost:5000/api/user/product/details?prodId="+pid.id);
			// this.xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
			// this.xhr.responseType="json";
			// this.xhr.send();
			window.location.href="productdetails_1.html";
		}
		
		// txt+='<button class="box" type="submit" name=sel value='+result[i].productId+'onclick="display()">';
		// 	 		 	// txt+='<button style="background:white; border:6px solid red; height=400px; width=500px;" type="submit" name=sel value='+result[i].productId+'onclick="display()">';
		// 				txt += "<img src='Pictures/"+result[i].productImage+"' height='250px' width='250px'>";
					
		// 	 		 	txt += '<div class="desc"><strong>'+result[i].productName+'</strong></br>'+'<b>PRICE :</b>'+result[i].productPrice+'</div>';
			 		 	
		// 				txt+='</button>';
						  
		// 		}
		// 		txt+='</div>';
		// 		document.getElementById("main4").innerHTML = txt;	
		// 	}
			
		
		
		
		</script>
		<body onload="send()">


    
    <div id="main">
	<marquee id="mar" width="100%" direction="right" height="150px">
 FREE SHIPPING FOR INTERNATIONAL AND DOMESTIC ORDERS ABOVE $60 !!
	</marquee>
		<!-- <div id="c">
			<li><a class="purchase" href="#about">CART</a></li>
		</div> -->
        </div>
    
        

    <div id="main2">

		<div id="d2">
				<a class="home" href="home.php">KYLIE COSMETICS</a></li>
		</div>
    
        <div id="d">
          
         <ul>
            <li><a class="nav" href="#home">HOLIDAY COLLECTION</a></li>&nbsp;&nbsp;
			<li><a class="nav" href="#news">GIFT GUIDE</a></li>&nbsp;&nbsp;
			<li><a class="nav" href="#home">SHOP</a></li>&nbsp;&nbsp;
			<li><a class="nav" href="#home">SALE</a></li>&nbsp;&nbsp;
			<li><a class="nav" href="#home">BUNDLES & SETS</a></li>&nbsp;&nbsp;
			<li><a class="nav" href="#home">BACK IN STOCK</a></li>&nbsp;&nbsp;
			<li><a class="nav" href="#home">KYLIES FAVE</a></li>&nbsp;&nbsp;
			<!-- <li><a class="cart" href="#home">CART</a></li> -->

		</ul>         
		</div>
		<div id="ca">
				<div class="my-2 my-lg-0 text-center">
						<a href="cart.html">
							<button class="btn btn-light" type="button" id="ref">
								<span><i class="fas fa-shopping-cart"></i></span>
								<span class="badge badge-danger"><?php echo @count(2); ?></span>
							</button>
						</a>
					</div>
		</div>

                
    
    
    </div>

    <div id="main3">

			<tr><td></td><td></td><input type="text" name="search" id="search" placeholder="SEARCH  CATEGORY " onkeypress="obje.getTerm()" /></td></div></tr>
			<tr><td></td><td><div id="container"></td></div></tr>  
	</div>

	<div id="main4">

			
	</div>

	<div id="main8">
        <div class="l1">
            <div class="ls">
                <p class="p13"> ABOUT US</p>
                <p class="p14"> Beta business plan growth hacking low hanging fruit ecosystem hypothesis investor ramen</p>
                <p class="p14"> MVP equity research &amp;development early adopters burn rate backing funding. </p>
            
            </div>
            <div class="rs">
                <p class="p15"> COMPANY</p>
                
                    <p class="p16"> BLOG </p>
                    <p class="p16"> PRIVACY POLICY </p>
                    <p class="p16"> TERMS OF USE </p>
                    <p class="p16"> ADVERTISE </p>
                    <p class="p16"> CONTACT US </p>
                    
                
            </div>
            
        </div>
        <div class="r1">
            <p class="p17"> SUBSCRIBE TO NEWSLETTER</p>
            <form>
            <input id="email" type="email" name="email" placeholder="e-mail address">
            </form>
            <div>
                    <ul class="z">
                        <li><a class="subscribe" href="#about">SUBSCRIBE</a></li>
                    </ul>
                </div>
        
            
            <p class="p19"> We promise that we will never share your e-mail address or other identifiable information with any third party company. </p>
            
        </div>
    </div>
			
	</body>
	</html>