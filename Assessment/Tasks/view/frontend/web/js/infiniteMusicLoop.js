define([
    "jquery",
    "Magento_Customer/js/customer-data",
    "./mediaelement-and-player.min"
], function ($, customerData) {
    var mediaPlayer, mediaPlayerPoolKey = '__infiniteMediaPlayer__', pluginSourceName = 'mediaelementplayer';
    return function (config, element) {
        mediaPlayer = $(element)[pluginSourceName]({
            success: function (_, player) {
                var previousTime = customerData.get(mediaPlayerPoolKey)();
                if(previousTime.time) {
                    player.currentTime = previousTime.time;
                }

                // Not use autoplay attribute on audion but start play from here in order to lift off from where it stops OR from start
                player.play();
            }
        }).data(pluginSourceName);


        window.onbeforeunload = function (e) {
            customerData.set(mediaPlayerPoolKey, {time: mediaPlayer.currentTime});
        }
    }
});