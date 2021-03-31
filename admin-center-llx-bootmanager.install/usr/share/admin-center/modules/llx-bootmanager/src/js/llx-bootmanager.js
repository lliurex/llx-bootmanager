
// Classe N4dChatSession

function LlxNetBootManagerClient(){
   this.BootOrder=null;
   this.AvailableList=null;
}

// Methods

LlxNetBootManagerClient.prototype.showTimer = function(){

    let credentials="";
    let n4dclass="LlxBootManager";
    let n4dmethod="getBootTimer";
    let arglist=[];

    Utils.n4d(credentials, n4dclass, n4dmethod, arglist, function(response){
        let slider=$("#llx_bootmanager_slider")[0];
       
        noUiSlider.create(slider, {
            start: [ response/10 ],
            step: 5,
            range: {
                'min': [  0 ],
                'max': [ 120 ]
            }
        });
         
        //$( "#time" ).val( (response/10)+" s");
        slider.noUiSlider.on('update', function(values, handle){
            $("#llx_bootmanager_timeout").val(values[handle]+" s");
        });
    },0);  
}

LlxNetBootManagerClient.prototype.sortList = function(){
    let self=this;
    // Wait for BootOrder and AvailableList are not empty
    if (self.BootOrder && self.AvailableList) {
        // Populate Lists
        $("#llx_bootmanager_BootList").empty();
        $("#llx_bootmanager_AvailableOptions").empty();
        // Populate Boot List
        for (i in self.BootOrder) {
            let itemclass="llx_bootmanager_ltsp";
            if (self.BootOrder[i].indexOf("bootfromhd")>-1) itemclass="llx_bootmanager_bootfromhd";
            if (self.BootOrder[i].indexOf("netinstall")>-1) itemclass="llx_bootmanager_netinstall";
            // Check if BootOrder[i] is in the Available List
            let boot_label=""
        
            for (j in self.AvailableList) {
                if (self.BootOrder[i]==self.AvailableList[j]["id"]) {
                    boot_label=self.AvailableList[j]["label"];
                    if (boot_label.indexOf("menu label")>=0){
                    boot_label=self._("llx_bootmanager.menu_label")+boot_label.substring(10,boot_label.length);
                    }
                    self.AvailableList.splice(j, 1);
                    break;
                }
            }
            console.log('Creating boot entry: '+boot_label+' type: '+self.BootOrder[i]);
            let listitem=$(document.createElement("li")).html(boot_label).attr("id", self.BootOrder[i]).addClass("llx_bootmanager_menuEntry").addClass(itemclass);
            // bootfromhd netinstall
            // only append  Boot List if boot item is available (has found it on available list)
            if (boot_label!="") $("#llx_bootmanager_BootList").append(listitem);
        }
        // Populate Available List
        for (i in self.AvailableList) {
            let itemclass="llx_bootmanager_ltsp";
            if (self.AvailableList[i]['id'].indexOf("bootfromhd")>-1) itemclass="llx_bootmanager_bootfromhd";
            if (self.AvailableList[i]['id'].indexOf("netinstall")>-1) itemclass="llx_bootmanager_netinstall";
            let listitem=$(document.createElement("li")).html(self.AvailableList[i]['label']).attr("id", self.AvailableList[i]['id']).addClass("llx_bootmanager_menuEntry").addClass(itemclass);
            // bootfromhd netinstall

            $("#llx_bootmanager_AvailableOptions").append(listitem);
            //listitem=document.createElement("li").html();
        }
        // Make Lists Sortable
        $( "#llx_bootmanager_BootList, #llx_bootmanager_AvailableOptions" ).sortable({
            placeholder: "llx_bootmanager_ui-sortable-placeholder",
            connectWith: ".connectedSortable",
            /*start: function(event, item){
            list_of_origin=event.target.id;
            },*/
            receive: function(event, ui) {
                let sourceList = $(ui.sender)[0].id;
                let targetList = $(this)[0].id;
                //console.log(sourceList);
                //console.log(targetList);
                if (sourceList==targetList) {
                    // Nothing to do...
                } else{  // Different Lists, let's swap!
                    if (sourceList=="llx_bootmanager_AvailableOptions") {
                        div=$("#llx_bootmanager_BootList").find("li")
                        let element=null
                        for (i=0;i<div.length;i++) {
                            if((typeof($(div[i]).children()[0]))!="undefined"){
                                if ($(div[i]).children()[0].className=="llx_bootmanager_empty"){
                                    element=$(div[i]).children()[0]
                                }
                            }
                        }
                        let newelement=$(document.createElement("li")).addClass("i-sortable-handle");
                        $(newelement).append($(element).clone());
                        //console.log(element);
                        $(element).parent().remove();
                        $("#llx_bootmanager_AvailableOptions").append(newelement)
                    } else {
                        div=$("#llx_bootmanager_AvailableOptions").find("li")
                        let element=null;                        
                        for (i=0;i<div.length;i++) {
                            //alert(typeof($(div[i]).children()[0]));
                            if((typeof($(div[i]).children()[0]))!="undefined"){
                                if ($(div[i]).children()[0].className=="llx_bootmanager_empty"){
                                    element=$(div[i]).children()[0]
                                }
                            }
                        }
                        let newelement=$(document.createElement("li")).addClass("i-sortable-handle");
                        $(newelement).append($(element).clone());
                        $(element).parent().remove();
                        $("#llx_bootmanager_BootList").append(newelement);
                    }
                } // End elsedifferent lists*/
            }
         }).disableSelection();
    }
}

LlxNetBootManagerClient.prototype._=function _(text){
    //console.log(i18n);
    return ( i18n.gettext("llx-bootmanager", text));
};
    
LlxNetBootManagerClient.prototype.populateGui=function populateGui(){
    let self=this;
    
    $("#llx_bootmanager_MainFrame").show();
        self.showLists();
        self.showTimer();

        $("#llx_bootmanager_btSave").bind('click', function(){
            let new_boot_order=[];

        $('ul#llx_bootmanager_BootList li').each(function(index, element) {
            //alert ($(element).find(".llx_bootmanager_menuEntry").prop("id"));
            //console.log($(element).find(".llx_bootmanager_menuEntry"));
            let label=$(element).prop("id");
            new_boot_order.push(label);
        });

        // Save List
        //parameter_list = [[sessionStorage.username, sessionStorage.password],"LlxBootManager"].concat(new_boot_order);
        //console.log(new_boot_order);

        let credentials=[sessionStorage.username, sessionStorage.password];
        let n4dclass="LlxBootManager";
        let n4dmethod="setBootOrder";
        let arglist=new_boot_order;
        
        let time=$("#llx_bootmanager_slider")[0].noUiSlider.get();
         
        Utils.n4d(credentials, n4dclass, n4dmethod, arglist, function(response){
        
            credentials=[sessionStorage.username, sessionStorage.password];
            n4dclass="LlxBootManager";
            n4dmethod="setBootTimer";
            arglist=[time*10];

            Utils.n4d(credentials, n4dclass, n4dmethod, arglist, function(response){
                msg=self._("llx_bootmanager.done");
                Utils.msg(msg, MSG_SUCCESS);
            });
        });
    });
}
   
LlxNetBootManagerClient.prototype.showLists = function showLists(){

   // Load Templates
    let self=this;
    
    $.get("http://"+sessionStorage['server']+"/ipxeboot/getmenujson.php", function(data){
        console.log('Object from getmenujson.php: '+data)
        self.AvailableList=JSON.parse(data);
        credentials="";
        n4dclass="LlxBootManager";
        n4dmethod="getBootOrder";
        arglist=[];
        Utils.n4d(credentials, n4dclass, n4dmethod, arglist, function(response){
            //self.BootOrder=response[0];
            self.BootOrder=response;
            self.sortList();
        },0);
    });
};

let LlxNetBootManager=new LlxNetBootManagerClient();
//$("body").css("background","#ffffff");
//alert(document.getElementsByTagName("body"));
$(document).on("moduleLoaded", function(e, args){
    let moduleName="llx-bootmanager";
    //console.log(args["moduleName"]);
    if(args.moduleName===moduleName){
        setTimeout(function(){
            LlxNetBootManager.populateGui();
            $.material.init();
        }, 100); // wait for ui will be ready
   }
});

$(document).on("componentClicked", function(e, args){
    let moduleName="llx-bootmanager";
    if(args.component===moduleName){
        // Refresh view
        //LlxNetBootManager.BootOrder=null;
        //LlxNetBootManager.AvailableList=null;
        setTimeout(function(){
            LlxNetBootManager.showLists();
            $.material.init();
        }, 100); // wait for ui will be ready
    }
});



