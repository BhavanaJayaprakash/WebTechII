from flask import Flask,request,jsonify,make_response
from flask_sqlalchemy import SQLAlchemy
from flask_cors import CORS
import os
from datetime import datetime
import re
import base64
import math
app = Flask(__name__)
CORS(app)
app.config['SECRET_KEY'] = 'itissecret'
app.config['SQLALCHEMY_DATABASE_URI']= os.environ.get('DATABASE_URL','sqlite:////home/bhavana/Desktop/WT2/cosmetic_data.db')
db=SQLAlchemy(app)

class User(db.Model):
	# userId=db.Column(db.String(20),db.Sequence('seq_reg_id', start=1, increment=1),primary_key=True)
	emailId=db.Column(db.String(100),primary_key=True)
	password=db.Column(db.String(100))
	name=db.Column(db.String(50))
	phoneNum=db.Column(db.String(10))
	address=db.Column(db.String(200))

class Product(db.Model):
	productId=db.Column(db.String(20),primary_key=True)
	name=db.Column(db.String(50))
	image=db.Column(db.String(1000))
	category=db.Column(db.String(100))
	price=db.Column(db.Float)
	brand=db.Column(db.String(100))
	description=db.Column(db.String(500))
	numPurchase=db.Column(db.Integer)

class Cart(db.Model):
	userId=db.Column(db.String(20),primary_key=True)
	productId=db.Column(db.String(20),primary_key=True)
	quantity=db.Column(db.Integer)
	subtotal=db.Column(db.Float)

class Purchase(db.Model):
	userId=db.Column(db.String(20),primary_key=True)
	date=db.Column(db.String(20),primary_key=True)
	numProduct=db.Column(db.Integer)
	total=db.Column(db.Float)
	paymentMode=db.Column(db.String(50))
	paid=db.Column(db.String(20))

@app.route("/api/user/login",methods=['POST'])
def validate():
	data=request.get_json()
	try:
		data['emailId']
		data['password']
	except:
		return jsonify({"msg":"enter properly"}),400
	user=User.query.filter_by(emailId=data['emailId'],password=data['password']).first()
	if user:
		return jsonify({"msg":"user logged in successfully","userId":data['emailId'],"name":user.name}),200
	return jsonify({"msg":"no user found"}),403

@app.route("/api/user/add/product",methods=['POST'])
def AddProductToCart():
	data=request.get_json()
	try:
		data["userId"]
		data["productId"]
		data["quantity"]
	except:
		return jsonify({"msg":"enter properly"}),400
	price=Product.query.filter_by(productId=data["productId"]).first().price
	cart=Cart.query.filter_by(userId=data["userId"],productId=data["productId"]).first()
	print("Q=",data["quantity"])
	if cart:
		subtot=cart.quantity+data["quantity"]
		print("subtotal = ",subtot)
		if(subtot >= 1):
			subtotal=price*(subtot)
			Cart.query.filter_by(userId=data["userId"],productId=data["productId"]).update(dict(quantity=(cart.quantity+data["quantity"]),subtotal=subtotal))
			db.session.commit()
		else:
			subtotal=price
			Cart.query.filter_by(userId=data["userId"],productId=data["productId"]).update(dict(quantity=1,subtotal=subtotal))
			db.session.commit()
	else:
		subtotal=price
		some=Cart(userId=data["userId"],productId=data["productId"],quantity=1,subtotal=subtotal)
		db.session.add(some)
		db.session.commit()
	return jsonify({"msg":"product added to the cart successfully"}),201

# @app.route("/api/user/update/product",methods=['POST'])
# def UpdateProductQuantity():
# 	data=request.get_json()
# 	try:
# 		data["userId"]
# 		data["productId"]
# 		data["quantity"]
# 	except:
# 		return jsonify({"msg":"enter properly"}),400
# 	cart=Cart.query.filter_by(userId=data["user"])

@app.route("/api/user/cart",methods=['GET'])
def ViewCart():
	try:
		data=request.args["userId"]
	except:
		return jsonify({"msg":"enter properly"}),400
	products=Cart.query.filter_by(userId=data).all()
	if products:
		prod={}
		for product in products:
			price=Product.query.filter_by(productId=product.productId).first()
			prod[product.productId]={"name":price.name, "brand":price.brand, "price":price.price, "quantity":product.quantity, "subtotal":product.subtotal}
		return jsonify(prod),200
	return jsonify({"msg":"No items in cart"}),405

@app.route("/api/user/product",methods=['GET'])
def GetProduct():
	products = Product.query.all()
	if products:
		prod={}
		for product in products:
			prod[product.productId]={"productName":product.name, "productImage":product.image, "productPrice":product.price}
		print(prod)
		return jsonify(prod),200
	return jsonify({"msg":"No products"}),405

@app.route("/api/user/search/product/category",methods=['GET'])
def GetProductByCategory():
	try:
		data=request.args['prodCat']
	except:
		return jsonify({"msg":"enter properly"}),400
	products = Product.query.filter_by(category=data).all()
	if products:
		prod={}
		for product in products:
			prod[product.productId]={"productName":product.name, "productImage":product.image, "productPrice":product.price}
		print(prod)
		return jsonify(prod),200
	return jsonify({"msg":"No products"}),405

@app.route("/api/user/product/details",methods=['GET'])
def GetProductDetails():
	try:
		data=request.args['prodId']
	except:
		return jsonify({"msg":"enter properly"}),400
	product = Product.query.filter_by(productId=data).first()
	if product:
		return jsonify({"prodName":product.name, "prodImage":product.image, "prodCat":product.category, "prodPrice":product.price, "prodBrand":product.brand, "prodDesc":product.description}),200
	return jsonify({"msg":"No product"}),405

@app.route("/api/user/register",methods=['POST'])
def RegisterUser():
	data=request.get_json()
	try:
		data['email']
		data['password']
		data['name']
		data['phoneNo']
		data['address']
	except:
		return jsonify({"msg":"enter properly"}),400
	user=User.query.filter_by(emailId=data['email']).first()
	if user:
		return jsonify({"msg":"email already used"}),401
	userdel=User(emailId=data['email'],password=data['password'],name=data['name'],phoneNum=data['phoneNo'],address=data['address'])
	db.session.add(userdel)
	db.session.commit()
	return jsonify({"msg":"account created successfully","userId":data['email'],"name":data['name']}),200
	

@app.route("/api/user/purchase",methods=['POST'])
def MakePurchase():
	data=request.get_json()
	try:
		data['userId']
		data['date']
		data['total']
		data['mode']
		data['paid']
		data['nProd']
		data['listId']
	except:
		return jsonify({"msg":"enter properly"}),400
	# purchases = Purchase.query.filter_by(userId=data['userId'],productId=data['prodId']).all()
	# for purchase in purchases:
	purchase=Purchase(userId=data['userId'],date=data['date'],total=data['total'],paymentMode=data['mode'],paid=data['paid'],numProduct=data['nProd'])
	db.session.add(purchase)
	db.session.commit()
	for prod in data['listId']:
		Product.query.filter_by(productId=prod).update(dict(numPurchase=(Product.numPurchase+1)))
		db.session.commit()
	return jsonify({"msg":"order placed successfully","userId":data['userId']}),200

@app.route("/api/user/delete/item",methods=['DELETE'])
def DeleteCartItem():
	data=request.get_json()
	try:
		data['userId']
		data['prodId']
	except:
		return jsonify({"msg":"enter properly"}),400
	item=Cart.query.filter_by(userId=data['userId'],productId=data['prodId']).first()
	if item:
		db.session.delete(item)
		db.session.commit()
		
	cart=Cart.query.filter_by(userId=data['userId']).first()
	if cart:
		return jsonify({'msg':'item deleted',"msg1":1}),200
	else:
		return jsonify({'msg':'item deleted',"msg1":0}),200
	return jsonify({'msg':'item not found'}),403

@app.route("/api/user/delete/cart",methods=['DELETE'])
def DelCart():
	data=request.get_json()
	try:
		data['userId']
	except:
		return jsonify({"msg":"enter properly"}),400
	carts=Cart.query.filter_by(userId=data['userId']).all()
	if carts:
		for cart in carts:
			db.session.delete(cart)
			db.session.commit()
	return jsonify({'msg':'cart deleted'})


@app.route("/api/product/top",methods=['GET'])
def TopProducts():
	products = Product.query.order_by(Product.numPurchase.desc()).limit(6).all()
	if products:
		prod={}
		for product in products:
			prod[product.productId]={"productName":product.name, "productImage":product.image, "productPrice":product.price, "numPurchase":product.numPurchase}
		return jsonify(prod),200
	return jsonify({"msg":"No products"}),405

	
	


if __name__=='__main__':
    app.run(host="0.0.0.0",debug=True)
