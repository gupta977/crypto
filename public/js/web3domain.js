async function w3d_getDomain_byID(id,web3)
{ 
    const ABI_ID = [
    {
        "inputs": [
          {
            "internalType": "uint256",
            "name": "tokenId",
            "type": "uint256"
          }
        ],
        "name": "titleOf",
        "outputs": [
          {
            "internalType": "string",
            "name": "",
            "type": "string"
          }
        ],
        "stateMutability": "view",
        "type": "function",
        "constant": true
      }
  ];
    const contractAddress = '0x30EB8fF1fa192030d2456264453DAAF7fcC54d39';
    const myContract = new web3.eth.Contract(ABI_ID, contractAddress);

    myContract.methods.titleOf(id).call().then(function(domain) {

       console.log(domain);

    });
   
}