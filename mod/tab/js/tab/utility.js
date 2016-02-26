module.exports = (function() {
    return {
        /**
         * @param qs The string to look for. Defaults to document.location.search
         * @returns {{}} Object containing mapping of url ids and their corresponding value.
         */
        get_query_params: function (qs) {
            qs = qs || document.location.search;
            qs = qs.split('+').join(' ');

            var params = {},
                tokens,
                re = /[?&]?([^=]+)=([^&]*)/g;

            while (tokens = re.exec(qs)) {
                params[decodeURIComponent(tokens[1])] = decodeURIComponent(tokens[2]);
            }

            return params;
        }
    }
})();