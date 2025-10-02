define([
    "dojo/_base/declare",
], function(declare){
return declare ("bgagame.SkillCounter",null, {
    nedded: null,
    
    constructor: function(skillcost){
        this.needed = [];
        for(var skill in skillcost){
            this.needed[skill] = skillcost[skill];
        }
    },

    isComplete: function(){
        var result = true;
        this.needed.array.forEach(element => {
            if(element > 0){result = false;}
        });
        return result;
    }
});
});