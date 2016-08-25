


jQuery(function($) {
    var wordCounts = {};
    $("#page").on( 'keyup', "#form_content", function() {
    
        var matches = this.value.match(/\b/g);
        wordCounts[this.id] = matches ? matches.length / 2 : 0;
        var finalCount = 0;
        $.each(wordCounts, function(k, v) {
            finalCount += v;
        });
        $("input[name='word_count']").val(finalCount)
    }).keyup();
});