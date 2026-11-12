( function ( $ ) {
	var $lista = $( '#parafia-gk-lista' );
	if ( ! $lista.length ) {
		return;
	}
	var $pole = $( '#parafia-galeria-pole' );
	var $okladka = $( '#parafia-galeria-okladka' );
	var $licznik = $( '#parafia-gk-licznik' );
	var $postep = $( '#parafia-gk-postep' );
	var wzor = document.getElementById( 'parafia-gk-wzor' );
	var postId = $( '.parafia-gk' ).data( 'post' );

	function odmiana( n ) {
		if ( n === 1 ) {
			return '1 zdjęcie';
		}
		var r10 = n % 10;
		var r100 = n % 100;
		return n + ( r10 >= 2 && r10 <= 4 && ( r100 < 12 || r100 > 14 ) ? ' zdjęcia' : ' zdjęć' );
	}

	function zapisz() {
		var ids = $lista.children( '[data-id]' ).map( function () {
			return $( this ).attr( 'data-id' );
		} ).get().filter( function ( id ) {
			return id && id !== '0';
		} );
		$pole.val( ids.join( ',' ) );
		if ( ids.indexOf( String( $okladka.val() ) ) === -1 ) {
			$okladka.val( ids.length ? ids[ 0 ] : '' );
		}
		$lista.children().removeClass( 'is-okladka' );
		$lista.children( '[data-id="' + $okladka.val() + '"]' ).addClass( 'is-okladka' );
		$licznik.text( ids.length ? odmiana( ids.length ) + ' w galerii' : 'Galeria nie ma jeszcze zdjęć.' );
		$( '.parafia-gk' ).toggleClass( 'is-pusta', ! ids.length );
	}

	function dodaj( id, url ) {
		id = String( id );
		if ( $lista.children( '[data-id="' + id + '"]' ).length ) {
			return null;
		}
		var $el = $( wzor.content.firstElementChild.cloneNode( true ) );
		$el.attr( 'data-id', id );
		$el.find( 'img' ).attr( 'src', url || '' );
		$lista.append( $el );
		return $el;
	}

	$lista.sortable( {
		items: '> li',
		tolerance: 'pointer',
		placeholder: 'parafia-gk__miejsce',
		update: zapisz,
	} );

	$lista.on( 'click', '.parafia-gk__usun', function () {
		$( this ).closest( 'li' ).remove();
		zapisz();
	} );
	$lista.on( 'click', '.parafia-gk__okladka', function () {
		$okladka.val( $( this ).closest( 'li' ).attr( 'data-id' ) );
		zapisz();
	} );

	var ramka;
	$( '#parafia-gk-biblioteka' ).on( 'click', function () {
		if ( ! ramka ) {
			ramka = wp.media( {
				title: 'Dodaj zdjęcia do galerii',
				button: { text: 'Dodaj do galerii' },
				library: { type: 'image' },
				multiple: 'add',
			} );
			ramka.on( 'select', function () {
				ramka.state().get( 'selection' ).each( function ( zal ) {
					var dane = zal.toJSON();
					var rozmiary = dane.sizes || {};
					dodaj( dane.id, ( rozmiary.thumbnail || rozmiary.medium || rozmiary.full || {} ).url || dane.url );
				} );
				zapisz();
			} );
		}
		ramka.open();
	} );

	if ( window.wp && wp.Uploader ) {
		var oczekujace = 0;
		new wp.Uploader( {
			container: $( '#parafia-gk-strefa' ),
			browser: $( '#parafia-gk-wybierz' ),
			dropzone: $( '#parafia-gk-strefa' ),
			params: { post_id: postId },
			plupload: {
				filters: {
					mime_types: [ { title: 'Zdjęcia', extensions: 'jpg,jpeg,png,webp,gif,heic' } ],
				},
			},
			added: function ( zal ) {
				oczekujace++;
				var $el = dodaj( 'tymczasowe-' + zal.cid, '' );
				if ( $el ) {
					$el.addClass( 'is-wgrywane' ).attr( 'data-id', '0' ).attr( 'data-cid', zal.cid );
				}
				$postep.prop( 'hidden', false ).text( 'Wgrywanie… zostało: ' + oczekujace );
			},
			progress: function () {},
			success: function ( zal ) {
				oczekujace = Math.max( 0, oczekujace - 1 );
				var dane = zal.toJSON ? zal.toJSON() : zal;
				var rozmiary = dane.sizes || {};
				var $el = $lista.children( '[data-cid="' + zal.cid + '"]' );
				if ( ! $el.length ) {
					$el = dodaj( dane.id, '' );
				}
				$el.removeClass( 'is-wgrywane' ).attr( 'data-id', dane.id ).removeAttr( 'data-cid' );
				$el.find( 'img' ).attr( 'src', ( rozmiary.thumbnail || rozmiary.medium || rozmiary.full || {} ).url || dane.url );
				$postep.text( oczekujace ? 'Wgrywanie… zostało: ' + oczekujace : 'Gotowe. Pamiętaj, aby kliknąć „Opublikuj” albo „Aktualizuj”.' );
				zapisz();
			},
			error: function ( komunikat, dane, plik ) {
				oczekujace = Math.max( 0, oczekujace - 1 );
				if ( plik && plik.attachment ) {
					$lista.children( '[data-cid="' + plik.attachment.cid + '"]' ).remove();
				}
				$postep.prop( 'hidden', false ).text( 'Nie udało się wgrać pliku: ' + ( komunikat || '' ) );
				zapisz();
			},
		} );
		$( '#parafia-gk-strefa' ).on( 'dragenter dragover', function () {
			$( this ).addClass( 'is-nad' );
		} ).on( 'dragleave drop', function () {
			$( this ).removeClass( 'is-nad' );
		} );
	}

	zapisz();
} )( jQuery );
