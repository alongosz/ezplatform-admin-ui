(function(moment) {
    const formatICUEx = /[GdayLqDeEc]+/g;
    const formatICUMap = {
        dd: 'DD',
        d: 'D',
        a: 'A',
        y: 'Y',
        yy: 'YY',
        yyyy: 'YYYY',
        LLLL: 'MMMM',
        LLL: 'MMM',
        LL: 'MM',
        L: 'M',
        q: 'Q',
        D: 'DDD',
        eeeeee: 'dd',
        eeee: 'dddd',
        eee: 'ddd',
        ee: 'E',
        e: 'E',
        EEEEEE: 'dd',
        EEEE: 'dddd',
        EEE: 'ddd',
        EE: 'ddd',
        E: 'ddd',
        cccccc: 'dd',
        cccc: 'dddd',
        ccc: 'ddd',
        cc: 'E',
        c: 'E',
    };
    const formatPHPEx = /[dDjlNSwzWFmMntLoYyaABgGhHisueIOPTZcrU]/g;
    const formatPHPMap = {
        d: 'DD',
        D: 'ddd',
        j: 'D',
        l: 'dddd',
        N: 'E',
        S: function() {
            return `[${this.format('Do').replace(/\d*/g, '')}]`;
        },
        w: 'd',
        z: function() {
            return this.format('DDD') - 1;
        },
        W: 'W',
        F: 'MMMM',
        m: 'MM',
        M: 'MMM',
        n: 'M',
        t: function() {
            return this.daysInMonth();
        },
        L: function() {
            return this.isLeapYear() ? 1 : 0;
        },
        o: 'GGGG',
        Y: 'YYYY',
        y: 'YY',
        a: 'a',
        A: 'A',
        B: function() {
            var thisUTC = this.clone().utc(),
                swatch = ((thisUTC.hours() + 1) % 24) + thisUTC.minutes() / 60 + thisUTC.seconds() / 3600;
            return Math.floor((swatch * 1000) / 24);
        },
        g: 'h',
        G: 'H',
        h: 'hh',
        H: 'HH',
        i: 'mm',
        s: 'ss',
        u: '[u]',
        e: '[e]',
        I: function() {
            return this.isDST() ? 1 : 0;
        },
        O: 'ZZ',
        P: 'Z',
        T: '[T]',
        Z: function() {
            return parseInt(this.format('ZZ'), 10) * 36;
        },
        c: 'YYYY-MM-DD[T]HH:mm:ssZ',
        r: 'ddd, DD MMM YYYY HH:mm:ss ZZ',
        U: 'X',
    };

    moment.fn.formatPHP = function(format) {
        console.warn('[DEPRECATED] formatPHP function is deprecated');
        console.warn('[DEPRECATED] it will be removed from ezplatform-admin-ui 2.0');
        console.warn('[DEPRECATED] use formatICU instead');
        return this.format(
            format.replace(formatPHPEx, (phpStr) => {
                return typeof formatPHPMap[phpStr] === 'function' ? formatPHPMap[phpStr].call(this) : formatPHPMap[phpStr];
            })
        );
    };

    moment.fn.formatICU = function(format) {
        const form = format.replace(formatICUEx, (icuStr) => {
            return typeof formatICUMap[icuStr] === 'function' ? formatICUMap[icuStr].call(this) : formatICUMap[icuStr];
        });

        return this.format(form);
    };
})(window.moment);
