var arr=[];							//给后台的编码合集
	
	function UpLoad(e){
		//alert(arr.length)
		if (arr.length > 2) {
			alert('最多添加5张')
			return false;
		} else{
			//alert('正常')
		}
		var f = e.files[0];
		fileType = f.type;
		if(/image\/\w+/.test(fileType)) {
			var fileReader = new FileReader();
			fileReader.readAsDataURL(f);
			fileReader.onload = function(event) {
				var result = event.target.result; //返回的dataURL 
				var image = new Image();
				image.src = result;
				console.log(result)
				console.log(f.size + '图片文件大小')
				//若图片大小大于1M，压缩后再上传，否则直接上传
				 if(f.size > 3000){ 
				 	var c=document.getElementById("canvas");
					var cxt=c.getContext("2d");
					var img=new Image();
					img.src=result;
					var width = img.width;
					var height = img.height;
					dic = height / width;
					//console.log(width+"------------------"+height+"---------"+dic )
					c.width = 200;  //图片压缩的标准，这里是按照定款200px计算
					c.height = 200;
					//console.log(width+'--------'+height)
					cxt.clearRect(img,0,0,0,0);
					cxt.drawImage(img,0,0,200, 	200);
					var finalURL = c.toDataURL(img,1);
					//alert(c.toDataURL(img,0.2))
					//$("#imgs").attr('src',finalURL)
					//console.log(fileType)
					console.log('画布大小：'+finalURL.length)
					arr.push(finalURL+';')
					//arr += finalURL+';'
					console.log(finalURL)
					console.log(arr.length+'图片数量')
	           }else{
	           		//alert('图片小')
	            }
			}
		}
	}
	
	
	
	var imgurl = document.getElementById('demo_input1');
	var result= document.getElementById("result"); 
	if ( typeof(FileReader) === 'undefined' ){
      result.innerHTML = "抱歉，你的浏览器不支持 FileReader，请使用现代浏览器操作！"; 
      input.setAttribute('disabled','disabled'); 
    }else{
      imgurl.addEventListener('change',readFile,false);
    } 
    
    function readFile(){
    	var file1 = this.files[0];
    //这里我们判断下类型如果不是图片就返回 去掉就可以上传任意文件  
        if(!/image\/\w+/.test(file1.type)){
       		alert("请确保文件为图像类型"); 
        		return false; 
        }else{
       	
        }
    // if(!/image\/\w+/.test(file2.type)){
    //   alert("请确保文件为图像类型"); 
    //   return false; 
    // }
    // if(!/image\/\w+/.test(file3.type)){
    //   alert("请确保文件为图像类型"); 
    //   return false; 
    // }
    var reader = new FileReader(); 
    reader.readAsDataURL(file1); 		// 编码
    var fl;
    reader.onload = function(e){ 
    	//alert(arr.length)
    	var asd = "<section class='up-section fl 0'><span class='up-span'></span><img class='close-upimg' src='img/a7.png' onclick='closeimg($(this))'><img class='up-img' src="+this.result+"  /></section>"
        //result.innerHTML = this.result; 
        if (arr.length == 0) {					//判断第一次添加图片
        	console.log(arr.length)
        	$(".upimg-div").prepend(asd)
        	
       		//arr.push(this.result)
        }else{
        	for (var i = 0; i < arr.length; i++){		//遍历数组 查找上传图片是否已存在
        		if (arr[i] == this.result) {
        			fl = 0
        		}
        		
        	}
        	if (fl == 0){
	        		console.log('相同图片不可添加')
	        		alert('失败')
	        		return false;
	        		//
	        		
	        	} else{
	        		
	        		$(".upimg-div").prepend(asd)
	       			//arr.push(this.result)
	        		console.log('添加成功')
	        	}
        }
//      $("#submit").click(function(){
//      	var imglength = $(".imgdiv img").length;
//      	console.log(imglength)
//      	//return false;
//      })
        
        // alert(this.result
//      if(arr.length==0){
//      	arr[0]=this.result;
//      }else if(arr.length==1){
//      	arr[1]=this.result;
//      }else if(arr.length==2){
//      	arr[2]=this.result;
//      }
//      var str="";
//      for (var i =0;i<arr.length;i++){
//      	if(i==0){
//      		str=arr[i];
//      	}else{
//      		str=str+"|"+arr[i];
//      	}
//      	//console.log(i+'i的值')
//      };
        // alert(document.getElementById("result").value)
        //document.getElementById("result").value=arr+'--';
        
        //console.log(result)
//     var length = $("#result").text().length
//      console.log('编码大小：'+length)
    }
  } 
  	//删除图片
//  $(document).on('click','.close-upimg',function(){
//  	//alert('---')
//  	//$("#result").val('')
//  	//var imgIndexs = $(".upimg-div .close-upimg").length
//  	//alert(imgIndexs)
//  	//var imgIndex = $(this).parent('upimg-div').find('.close-upimg').index()
//  	alert(arr)
//  	var imgIndex = $(this).parent().index()
//  	console.log(imgIndex)
//  	//alert(imgIndex)
//  	arr.splice(imgIndex)
//  	$(".upimg-div").find('.up-section').eq(imgIndex).remove()
//  	//$("#result").val(arr)
//  	//alert(arr)
//    	console.log(arr)
//  })

	function closeimg(v){
		var imgIndex = v.parent().index()
		//console.log(imgIndex)
		//arrse.splice(imgIndex,1)
		arr.splice(imgIndex,1)
		$(".upimg-div").find('.up-section').eq(imgIndex).remove()
		console.log(arr.length)
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
