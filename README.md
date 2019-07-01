# Crypto Currency Address Converter And Validation

### Install: 
composer require yusufkenar/crypto-currency-address-tools




### Converter Example of usage: 
    use CryptoCurrencyAddressTools\Converter\BCH;
 ##
    $new_address = BCH::old2new('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa');
      
    $old_address = BCH::new2old('bitcoincash:qp3wjpa3tjlj042z2wv7hahsldgwhwy0rq9sywjpyy', false);
      
   #### P2PK:
     
     old2new('1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa'): 'bitcoincash:qp3wjpa3tjlj042z2wv7hahsldgwhwy0rq9sywjpyy'
     
     new2old('bitcoincash:qp3wjpa3tjlj042z2wv7hahsldgwhwy0rq9sywjpyy'): '1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa'
     
   #### P2PKH:
     
     old2new('12higDjoCCNXSA95xZMWUdPvXNmkAduhWv'): 'bitcoincash:qqf2hrw93r9f64u8mhn7k22knknrcw3r3s0mkt0zxa'
     
     new2old('bitcoincash:qqf2hrw93r9f64u8mhn7k22knknrcw3r3s0mkt0zxa'): '12higDjoCCNXSA95xZMWUdPvXNmkAduhWv'
     
   #### P2SH:
     
     old2new('342ftSRCvFHfCeFFBuz4xwbeqnDw6BGUey'): 'bitcoincash:pqv60krfqv3k3lglrcnwtee6ftgwgaykpccr8hujjz'
     
     new2old('bitcoincash:pqv60krfqv3k3lglrcnwtee6ftgwgaykpccr8hujjz'): '342ftSRCvFHfCeFFBuz4xwbeqnDw6BGUey'

### Validation Example of usage: 

    use CryptoCurrencyAddressTools\ValidationFactory;
##
    $validator = new ValidationFactory();
    $currency = $validator->build('BTC', "32TLn1WLcu8LtfvweLzYUYU6ubc2YV9eZs");
    if ($currency->validate()) {
        //valid
    } else {
        //invalid
    }
        

### Supported crypto currencies

* `'BTC'`, Bitcoin
* `'BCH'`, Bitcoin Cash
* `'USDT'`, Usd Tether
* `'LTC'`, Lite Coin
* `'ETH'`, Ethereum
* `'ETC'`, Ethereum Classic
* `'DASH'`, DASH
* `'DOGE'`, Doge Coin
* `'DGB'`, DigiByte
* `'NEO'`, NEO
* `'XRP'`, XRP
* `'ZEC'`, Zcash
* `others` - Loading..
