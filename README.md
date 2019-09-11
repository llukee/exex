# exex

Frontend:
- Shortcode Spielplan (events) (Tabellenansicht)
  Datum | ShowID | LocationID | Seats
- Detailansicht für show (URL/show/[show-slug])
- Detailansicht für location (URL/location/[location-slug])

Posttypes:
- exbook_show (public)
- exbook_location (public)
- exbook_event
- exbook_mailtpl
- exbook_reservation

Datenbank Tabellen:

show:
- id
- name
- slug
- body

location:
- id
- name
- slug
- body
- (seats?)

event:
- id
- datetime
- show_id (show id)
- location_id (location id)
- seats
- resmail_id (mailtpl id)

mailtpl:
- id
- name
- subject
- message

reservation:
- id
- event_id (event id)
- quantity
- firstname
- name
- tel
- email
- status
- newsletter
- memo

Notizen:
- show = Veranstaltung = Theatherstück
- event = Vorstellung = Einzelne Vorstellung
1 Show hat mehrere Events
In Mailtemplates müssen Platzhalter eingesetzt werden können für die Variablen in der E-Mail Nachricht
Der Status in reservation ist standardmässig auf beschäftigt
Die Ausgabe der offenen Plätze ist die Differenz aus event_seats und Anzahl reservierter Plätze mit Status != storniert
