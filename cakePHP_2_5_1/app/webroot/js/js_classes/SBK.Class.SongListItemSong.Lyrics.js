/*global jQuery SBK alert */

SBK.SongListItemSong.Lyrics = SBK.SongListItemSong.extend({

    render: function () {
        var self = this, button_bar;

        jQuery('<span class="title">' + self.playlist.value_or_blank(self.data.title) + '</span>').appendTo(self.container);
        jQuery('<span class="id">(' + self.playlist.value_or_blank(self.data.id) + ')</span>').appendTo(self.container);
        button_bar = jQuery('<span class="button-bar"></span>').appendTo(self.container);
        jQuery('<span class="button lyrics">lyics</span>').appendTo(button_bar).click(function () {
            console.log(self.playlist);
            self.playlist.display_song({id: self.data.id, key: self.data.key, capo: self.data.capo, index: self.index, set_index: self.set.index});
        });
    }
});