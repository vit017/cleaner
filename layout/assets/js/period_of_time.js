//sessionStorage без проверки поддержки браузером 
//стиля нет никакого
var Period=
{  	create:function()
          {var cont=$('period');
		   var table=new Element('table',{});
		   cont.insert(table);
		   var tb=new Element('tbody');
		   table.insert(tb)
		   var tr1=new Element('tr');
		   var td1=new Element('td').update("Entered");
		   var d=new Date();
		   var td2=new Element('td',{'id':'time_input'}).update(d.toLocaleTimeString());
		   tb.insert(tr1);
		   tr1.insert(td1);
		   tr1.insert(td2);
		   var tr2=new Element('tr');
		   tb.insert(tr2);
		   var td3=new Element('td').update('Online');
		   var td4=new Element('td',{'id':'time_value'});
		   tr2.insert(td3);
		   tr2.insert(td4);
          }, 
    view:function(num)
           {if (typeof num== "undefined")
		       return "00";
		    else {var str=String(num);
			      if(str.length==1)
				     return ('0'+str) 
				  else return str 	 
			     }
           },		   
    translation:function(d) 
           {if (d>3600000)
		     {var h=Math.floor(d/3600000);
			  d=d%3600000
			 }
		    if(d>60000)
			 {var m=Math.floor(d/60000);
			  d=d%60000
			 }
		    var s=Math.round(d/1000);
		    var answer=this.view(h)+":"+this.view(m)+":"+this.view(s);
			return answer;
           },		   
    working:function()
          {if(sessionStorage.d) {var d=sessionStorage.d }else{var d=new Date();d=d.getTime();sessionStorage.d=String(d)}
		   this.create();		 		   
		   var self=this;
		   new PeriodicalExecuter(
		     function()
			  {var da=new Date();
			   da=da.getTime();
			   var change=da-Number(d);
               change=self.translation(change);			   
			   $('time_value').update(change);
			   //console.log(change);
			   if (change>"00:03:10"){
					//console.log(change);

				    var showPopUp=getCookie("showPopUp");
				    //console.log(showPopUp);
				    //if(!showPopUp){
			            setCookie("showPopUp","ok",30);

						//$(".callBackLink").trigger("click");
			            
				    //}
			   }
				
			   else
			   	console.log("not now");
			  },1
		   );

          }
} 
document.observe("dom:loaded",
   function()
    {Period.working();
	}   
   )


