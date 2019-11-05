function limitText() {
    document.getElementById('tweet').disabled=false;
    var ta= document.getElementById('textarea'),
        count= ta.value.length,
        ml= 140,
        remaining= ml - count,
        cc= document.getElementById('charcount_text');

    if(remaining <= 0) {
      cc.innerHTML = ml+' character limit reached.' ;
      document.getElementById('tweet').disabled=true;
    } else if(remaining <= 140) {
      cc.innerHTML = ml+' character limit, ' + remaining  + ' remaining.';
    } else {
      cc.innerHTML = '';
    }
  }